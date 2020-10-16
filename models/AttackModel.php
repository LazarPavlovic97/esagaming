<?php
namespace App\Models;

use App\Core\Field;
use App\Core\Model;
use App\Validators\NumberValidator;

class AttackModel extends Model {
    protected function getFields() {
        return [
            'attack_id'         => new Field((new NumberValidator())->setInteger()->setUnsigned()->setMaxIntegerDigits(11),false),
            'army_id'           => new Field((new NumberValidator())->setInteger()->setUnsigned()->setMaxIntegerDigits(11)),
            'serial_number'     => new Field((new NumberValidator())->setInteger()->setUnsigned()->setMaxIntegerDigits(11))
        ];
    }

    public function getFirstSerialNumberByGameID($gameID) {
        $sql = 'select *
                from attack
                where army_id in (select army_id from army where game_id = ?)
                order by serial_number asc
                limit 1;';

        $prep = $this->getDatabaseConnection()->getConnection()->prepare($sql);
        $res = $prep->execute([ $gameID ]);

        if($res) {
            return $prep->fetch(\PDO::FETCH_OBJ);
        }

        return [];
    }

    public function updateSerialNumbers($serial_number) {
        $sql = 'update attack
                set serial_number = serial_number - 1
                where serial_number > ?;';

        $prep = $this->getDatabaseConnection()->getConnection()->prepare($sql);
        $res = $prep->execute([ $serial_number ]);

        return [];
    }
}