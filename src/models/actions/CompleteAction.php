<?php


namespace app\models\actions;

use app\exception\WrongRoleException;
use app\models\Task;

class CompleteAction extends AbstractAction
{
    public static function getName(): string
    {
        return self::class;
    }

    public static function getCode(): string
    {
          return 'action_finish';
     }


    /**
     * @param int $initiator_id
     * @param Task $task
     * @return string
     * @throws WrongRoleException
     */
    public static function verifyAbility(int $initiator_id, Task $task): string
    {
        if ($task->getStatus() !== Task::STATUS_PROCESS) {
            return false;
        }

        if ($initiator_id !==  $task->getCustomer()) {
            throw new WrongRoleException('Роль не соответствует инициатору');
        }

        return true;
    }

}
