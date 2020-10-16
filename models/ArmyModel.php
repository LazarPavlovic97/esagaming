<?php
namespace App\Models;

use App\Core\Field;
use App\Core\Model;
use App\Validators\NumberValidator;
use App\Validators\StringValidator;

class ArmyModel extends Model {
    protected function getFields() {
        return [
            'army_id'           => new Field((new NumberValidator())->setInteger()->setUnsigned()->setMaxIntegerDigits(11),false),
            'name'              => new Field((new StringValidator())->setMinLength(0)->setMaxLength(255)),
            'current_units'     => new Field((new NumberValidator())->setInteger()->setUnsigned()->setMaxIntegerDigits(11)),
            'strategy'          => new Field((new StringValidator())->setMinLength(0)->setMaxLength(255)),
            'game_id'           => new Field((new NumberValidator())->setInteger()->setUnsigned()->setMaxIntegerDigits(11)),
            'serial_number'     => new Field((new NumberValidator())->setInteger()->setUnsigned()->setMaxIntegerDigits(11))
        ];
    }

    public function getNumberOfArmiesWithGivenName($name, $gameID) {
        $sql = 'select count(*) number
                from army
                where name = ? and game_id = ?;';

        $prep = $this->getDatabaseConnection()->getConnection()->prepare($sql);
        $res = $prep->execute([ $name,$gameID ]);

        if($res) {
            return $prep->fetchAll(\PDO::FETCH_OBJ);
        }

        return [];
    }

    public function getArmyThatCurrentlyAttacking($gameID) {
        $sql = 'select *
                from army
                where army_id = (select next_attack_army_id from next_attack where game_id = ?);';

        $prep = $this->getDatabaseConnection()->getConnection()->prepare($sql);
        $res = $prep->execute([ $gameID ]);

        if($res) {
            return $prep->fetch(\PDO::FETCH_OBJ);
        }

        return [];
    }

    public function getWeakestArmy($gameID) {
        $sql = 'select *
                from army
                where game_id = ?
                order by current_units asc
                limit 1;';

        $prep = $this->getDatabaseConnection()->getConnection()->prepare($sql);
        $res = $prep->execute([ $gameID ]);

        if($res) {
            return $prep->fetch(\PDO::FETCH_OBJ);
        }

        return [];
    }

    public function getSecondWeakestArmy($gameID) {
        $sql = 'select *
                from army
                where game_id = ?
                order by current_units asc
                limit 1, 1;';

        $prep = $this->getDatabaseConnection()->getConnection()->prepare($sql);
        $res = $prep->execute([ $gameID ]);

        if($res) {
            return $prep->fetch(\PDO::FETCH_OBJ);
        }

        return [];
    }

    public function getStrongestArmy($gameID) {
        $sql = 'select *
                from army
                where game_id = ?
                order by current_units desc
                limit 1;';

        $prep = $this->getDatabaseConnection()->getConnection()->prepare($sql);
        $res = $prep->execute([ $gameID ]);

        if($res) {
            return $prep->fetch(\PDO::FETCH_OBJ);
        }

        return [];
    }

    public function getSecondStrongestArmy($gameID) {
        $sql = 'select *
                from army
                where game_id = ?
                order by current_units desc
                limit 1, 1;';

        $prep = $this->getDatabaseConnection()->getConnection()->prepare($sql);
        $res = $prep->execute([ $gameID ]);

        if($res) {
            return $prep->fetch(\PDO::FETCH_OBJ);
        }

        return [];
    }

    public function getNextAttacker($armySerialNumber,$gameID) {
        $sql = 'select *
                from attack
                where serial_number = ? + 1
                and army_id in (select army_id from army where game_id = ?);';

        $prep = $this->getDatabaseConnection()->getConnection()->prepare($sql);
        $res = $prep->execute([ $armySerialNumber,$gameID ]);

        if($res) {
            return $prep->fetch(\PDO::FETCH_OBJ);
        }

        return [];
    }

    public function getNextAttackerIfDestroyed($armySerialNumber,$gameID) {
        $sql = 'select *
                from attack
                where serial_number = ? + 2
                and army_id in (select army_id from army where game_id = ?);';

        $prep = $this->getDatabaseConnection()->getConnection()->prepare($sql);
        $res = $prep->execute([ $armySerialNumber,$gameID ]);

        if($res) {
            return $prep->fetch(\PDO::FETCH_OBJ);
        }

        return [];
    }

    public function getAllArmiesWithUnitsDiffThanZero($gameID,$armyID) {
        $sql = 'select *
                from army
                where current_units > 0
                and game_id = ?
                and army_id <> ?;';

        $prep = $this->getDatabaseConnection()->getConnection()->prepare($sql);
        $res = $prep->execute([ $gameID,$armyID ]);

        if($res) {
            return $prep->fetchAll(\PDO::FETCH_OBJ);
        }

        return [];
    }

    public function getActiveArmies($gameID) {
        $sql = 'select *
                from army
                where game_id = ?
                and current_units > 0;';

        $prep = $this->getDatabaseConnection()->getConnection()->prepare($sql);
        $res = $prep->execute([ $gameID ]);

        if($res) {
            return $prep->fetchAll(\PDO::FETCH_OBJ);
        }

        return [];
    }

    public function getArmiesWhichAlreadyStartedGame($gameID) {
        $sql = 'select *
                from army
                where current_units < (select starting_units from game where game_id = ?)
                and game_id = ?;';

        $prep = $this->getDatabaseConnection()->getConnection()->prepare($sql);
        $res = $prep->execute([ $gameID,$gameID ]);

        if($res) {
            return $prep->fetchAll(\PDO::FETCH_OBJ);
        }

        return [];
    }
}