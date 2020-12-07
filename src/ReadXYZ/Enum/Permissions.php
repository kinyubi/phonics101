<?php


namespace App\ReadXYZ\Enum;


use App\ReadXYZ\Data\TrainersData;
use App\ReadXYZ\Helpers\PhonicsException;
use MyCLabs\Enum\Enum;

class Permissions extends Enum
{
    // Staff permissions
    const MODIFY_TRAINER_SCREEN  = 0x01;
    const MODIFY_STUDENTS_SCREEN = 0x02;
    const ADD_TRAINER_SCREEN     = 0x04;
    const ADD_STUDENT_SCREEN     = 0x08;
    const DELETE_TRAINER_SCREEN  = 0x10;
    const KEYCHAIN_SCREEN        = 0x20;
    const ANIMALS_SCREEN         = 0x40;
    const AWARD_ANIMALS_SCREEN   = 0x80;

    //admin permissions
    const CHANGE_TRAINER_TYPE = 0x100;
    const WARMUPS_SCREEN      = 0x200;
    const TAB_TYPES_SCREEN    = 0x400;
    const GAME_TYPES_SCREEN   = 0x800;
    const LESSONS_SCREEN      = 0x1000;
    const GROUPS_SCREEN       = 0x2000;

    const STAFF_MASK = 0xFF;
    const ADMIN_MASK = 0xFFFF;

    /**
     * @param string $trainer userName or trainerCode
     * @param $permission
     * @return bool
     */
    public function doesTrainerHavePermission($trainer, $permission): bool
    {
        if ($permission instanceof Permissions) {
            $permitBit = $permission->getValue();
        } elseif (Permissions::isValid($permission)) {
            $permitBit = $permission;
        } else {
            $value = (is_string($permission) || is_numeric($permission)) ? $permission : 'Non-primitive';
            throw new PhonicsException("$value is not a valid permission");
        }
        $trainerType = (new TrainersData())->getTrainerType($trainer);
        switch ($trainerType) {
            case TrainerType::ADMIN:
                $mask = Permissions::ADMIN_MASK;
                break;
            case TrainerType::STAFF:
                $mask = Permissions::STAFF_MASK;
                break;
            default:
                $mask = 0;
        }
        return ($mask & $permitBit) != 0;
    }
}
