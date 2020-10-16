<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\AutorunModel;
use App\Models\GameModel;

class MainController extends Controller {
    public function mainGet() {
        #Instances to models
        $gm = new GameModel($this->getDatabaseConnection());

        #Get all games
        $allGames = $gm->getAll();

        #Output on screen
        $this->set('allGames',$allGames);
    }

    public function mainPost() {
        #Instances to models
        $gm = new GameModel($this->getDatabaseConnection());
        $aum = new AutorunModel($this->getDatabaseConnection());

        #Get values from input form for starting units
        $startingUnits = filter_input(INPUT_POST, 'startingUnits', FILTER_SANITIZE_NUMBER_INT);

        #Get all games
        $allGames = $gm->getAll();

        #Check if number of starting units is between 80 and 100
        if($startingUnits < 80 || $startingUnits > 100 || $startingUnits == '') {
            $this->set('message','Starting units must be between 80 and 100!');
            $this->set('allGames',$allGames);
            return;
        }

        #Create game
        $res = $gm->add([
            'starting_units' => $startingUnits
        ]);

        if (!$res) {
            $this->set('message', 'Error...');
            return;
        }

        $resAutorun = $aum->add([
            'game_id' => $res
        ]);

        if (!$resAutorun) {
            $this->set('message', 'Error...');
            return;
        }

        header('Location: ' . BASE);
    }
}