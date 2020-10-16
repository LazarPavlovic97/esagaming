<?php
namespace App\Models;

use App\Core\Field;
use App\Core\Model;
use App\Validators\DateTimeValidator;
use App\Validators\NumberValidator;
use App\Validators\StringValidator;

class GameModel extends Model {
    protected function getFields() {
        return [
            'game_id'           => new Field((new NumberValidator())->setInteger()->setUnsigned()->setMaxIntegerDigits(11),false),
            'created_at'        => new Field((new DateTimeValidator()),false),
            'starting_units'    => new Field((new NumberValidator())->setInteger()->setUnsigned()->setMaxIntegerDigits(11)),
            'status'            => new Field((new StringValidator())->setMinLength(0)->setMaxLength(255))
        ];
    }
}