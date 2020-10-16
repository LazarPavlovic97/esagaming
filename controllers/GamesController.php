<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\ArmyModel;
use App\Models\AttackModel;
use App\Models\AutorunModel;
use App\Models\GameModel;
use App\Models\NextAttackModel;

class GamesController extends Controller {
    public function gamesGet($gameID) {
        #Instances to models
        $am = new ArmyModel($this->getDatabaseConnection());
        $gm = new GameModel($this->getDatabaseConnection());
        $aum = new AutorunModel($this->getDatabaseConnection());

        #Get status of the game
        $game = $gm->getById($gameID);

        #Get all armies for game with given ID
        $allArmies = $am->getAllByFieldName('game_id',$gameID);

        #Get current attacker
        $currentlyAttacking = $am->getArmyThatCurrentlyAttacking($gameID);

        #Get autorun status
        $autorun = $aum->getByFieldName('game_id',$gameID);

        #Output on screen
        $this->set('game',$game);
        $this->set('allArmies',$allArmies);
        $this->set('currentlyAttacking',$currentlyAttacking);
        $this->set('autorun',$autorun);
    }

    public function gamesPost($gameID) {
        #Instances to models
        $am = new ArmyModel($this->getDatabaseConnection());
        $gm = new GameModel($this->getDatabaseConnection());
        $atm = new AttackModel($this->getDatabaseConnection());
        $nam = new NextAttackModel($this->getDatabaseConnection());
        $aum = new AutorunModel($this->getDatabaseConnection());

        #Get all data for game with given ID
        $game = $gm->getById($gameID);

        #Get all armies for game with given ID
        $allArmies = $am->getAllByFieldName('game_id',$gameID);

        #Get current attacker
        $currentlyAttacking = $am->getArmyThatCurrentlyAttacking($gameID);

        #Create army
        if(isset($_POST['createArmy'])) {
            #Get values from input form for new army
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $strategy = filter_input(INPUT_POST, 'strategy', FILTER_SANITIZE_STRING);

            #Check if army with same name for this game already exists
            $getNumberOffArmiesWithGivenName = $am->getNumberOfArmiesWithGivenName($name,$gameID);

            if($name == '' || $strategy == '') {
                $this->set('message','Army must have name and strategy to be created!');
                $this->set('allArmies',$allArmies);
                $this->set('currentlyAttacking',$currentlyAttacking);
                return;
            }

            if($getNumberOffArmiesWithGivenName[0]->number > 0) {
                $this->set('message','Army with that name already exists for this game!');
                $this->set('allArmies',$allArmies);
                $this->set('currentlyAttacking',$currentlyAttacking);
                return;
            }

            #Create army
            $resArmy = $am->add([
                'name' => $name,
                'current_units' => $game->starting_units,
                'strategy' => $strategy,
                'game_id' => $game->game_id
            ]);

            if (!$resArmy) {
                $this->set('message', 'Error...');
                return;
            }

            #Insert into attack table
            $resAttack = $atm->add([
                'army_id' => $resArmy,
                'serial_number' => 1
            ]);

            if (!$resAttack) {
                $this->set('message', 'Error...');
                return;
            }

            #Insert into next attack table
            $alreadyStarted = $am->getArmiesWhichAlreadyStartedGame($gameID);

            if(empty($allArmies)) {
                $resNextAttackArmy = $nam->add([
                    'game_id' => $gameID,
                    'next_attack_army_id' => $resArmy
                ]);

                if (!$resNextAttackArmy) {
                    $this->set('message', 'Error...');
                    return;
                }
            } else if(count($allArmies) < 5 || count($alreadyStarted) == 0) {
                $nextAttackID = $nam->getByFieldName('game_id',$gameID);

                $resNextAttackArmy = $nam->editById($nextAttackID->next_attack_id,[
                    'next_attack_army_id' => $resArmy
                ]);

                if (!$resNextAttackArmy) {
                    $this->set('message', 'Error...');
                    return;
                }
            }

            #Change status of the game if 5 or more armies joined
            if((count($allArmies) + 1) == 5) {
                $resGame = $gm->editById($gameID,[
                    'status' => 'in progress'
                ]);

                if (!$resGame) {
                    $this->set('message', 'Error...');
                    return;
                }
            }

            header('Location: ' . BASE . 'games/' . $game->game_id);
        }

        #Run attack
        if(isset($_POST['runAttack'])) {
            #Check if game has minimum 5 armies before starting the game
            if(count($allArmies) < 5 && $game->status == 'waiting for players') {
                $this->set('message','Total number of armies must be minimum 5 to start the game!');
                $this->set('allArmies',$allArmies);
                $this->set('currentlyAttacking',$currentlyAttacking);
                return;
            }

            #Check if game is already finished
            if($game->status == 'finished') {
                $this->set('message','This game is finished!');
                $this->set('allArmies',$allArmies);
                $this->set('currentlyAttacking',$currentlyAttacking);
                return;
            }

            #Randomize opponent if current attacker strategy is to attack random opponent, or get weakest or strongest army
            if($currentlyAttacking->strategy == 'random') {
                $opponents = array();
                $allArmiesDiffThanZero = $am->getAllArmiesWithUnitsDiffThanZero($gameID,$currentlyAttacking->army_id);

                foreach($allArmiesDiffThanZero as $armies) {
                    if($armies->army_id != $currentlyAttacking->army_id) {
                        array_push($opponents,$armies->army_id);
                    }
                }

                $randomOpponentKey = array_rand($opponents);
                $currentOpponent = $am->getById($opponents[$randomOpponentKey]);
            } else if($currentlyAttacking->strategy == 'weakest') {
                $currentOpponent = $am->getWeakestArmy($gameID);
                if($currentlyAttacking->army_id == $currentOpponent->army_id) {
                    $currentOpponent = $am->getSecondWeakestArmy($gameID);
                }
            } else if($currentlyAttacking->strategy == 'strongest') {
                $currentOpponent = $am->getStrongestArmy($gameID);
                if($currentlyAttacking->army_id == $currentOpponent->army_id) {
                    $currentOpponent = $am->getSecondStrongestArmy($gameID);
                }
            }

            #Get win and loss chances and check the result
            $winChance = $currentlyAttacking->current_units / 100;
            $lossChance = 1 - $winChance;
            $set = [
                1 => $winChance,
                0 => $lossChance
            ];
            $result = $this->possibilityToWin($set);

            #Reduct opponent's units if attacker win
            if($result == 1) {
                #Check how many units will be reduced from opponent's army
                if(($currentlyAttacking->current_units % 2) == 1) {
                    if($currentlyAttacking->current_units == 1) {
                        $reducingUnits = 1;
                    } else {
                        $reducingUnits = $currentlyAttacking->current_units / 2 - 0.5;
                    }
                } else {
                    $reducingUnits = $currentlyAttacking->current_units / 2;
                }

                #Set number of new units for opponent's army
                if($reducingUnits >= $currentOpponent->current_units) {
                    $newUnits = 0;
                } else {
                    $newUnits = $currentOpponent->current_units - $reducingUnits;
                }

                #Get next attacker and modify serial numbers in database if units are reduce to 0
                if($newUnits == 0) {
                    $attackerSerialNumber = $atm->getByFieldName('army_id',$currentlyAttacking->army_id);
                    $nextAttacker = $am->getNextAttacker($attackerSerialNumber->serial_number,$gameID);

                    $opponentsSerialNumber = $atm->getByFieldName('army_id',$currentOpponent->army_id);

                    if($nextAttacker->army_id == $opponentsSerialNumber->army_id) {
                        $nextAttacker = $am->getNextAttackerIfDestroyed($attackerSerialNumber->serial_number,$gameID);
                    }

                    $resUpdate = $atm->updateSerialNumbers($opponentsSerialNumber->serial_number);
                } else {
                    $attackerSerialNumber = $atm->getByFieldName('army_id',$currentlyAttacking->army_id);
                    $nextAttacker = $am->getNextAttacker($attackerSerialNumber->serial_number,$gameID);
                }

                if(empty($nextAttacker)) {
                    $nextAttacker = $atm->getFirstSerialNumberByGameID($gameID);
                }

                #Update units in database
                $resOpponent = $am->editById($currentOpponent->army_id,[
                    'current_units' => $newUnits
                ]);

                if (!$resOpponent) {
                    $this->set('message', 'Error...');
                    return;
                }

                #Update next attack table
                $nextAttackTable = $nam->getByFieldName('game_id',$gameID);
                $resNextAttack = $nam->editById($nextAttackTable->next_attack_id,[
                    'next_attack_army_id' => $nextAttacker->army_id
                ]);

                if (!$resNextAttack) {
                    $this->set('message', 'Error...');
                    return;
                }
            } else {
                #If attack is unsuccessful
                $attackerSerialNumber = $atm->getByFieldName('army_id',$currentlyAttacking->army_id);
                $nextAttacker = $am->getNextAttacker($attackerSerialNumber->serial_number,$gameID);

                if(empty($nextAttacker)) {
                    $nextAttacker = $atm->getFirstSerialNumberByGameID($gameID);
                }

                #Update next attack table
                $nextAttackTable = $nam->getByFieldName('game_id',$gameID);
                $resNextAttack = $nam->editById($nextAttackTable->next_attack_id,[
                    'next_attack_army_id' => $nextAttacker->army_id
                ]);

                if (!$resNextAttack) {
                    $this->set('message', 'Error...');
                    return;
                }
            }

            #Finish game if only one army stays
            $activeArmies = $am->getActiveArmies($gameID);
            if(count($activeArmies) <= 1) {
                $resGameFinish = $gm->editById($gameID,[
                    'status' => 'finished'
                ]);

                if (!$resGameFinish) {
                    $this->set('message', 'Error...');
                    return;
                }
            }

            header('Location: ' . BASE . 'games/' . $game->game_id);
        }

        #Set autorun
        if(isset($_POST['autorun'])) {
            $autorun = $aum->getByFieldName('game_id',$gameID);

            if($autorun->status == 'false') {
                $autorunStatus = 'true';
            } else {
                $autorunStatus = 'false';
            }

            $resAutorun = $aum->editById($autorun->autorun_id,[
                'status' => $autorunStatus
            ]);

            if (!$resAutorun) {
                $this->set('message', 'Error...');
                return;
            }

            header('Location: ' . BASE . 'games/' . $game->game_id);
        }
    }
}