<?php
namespace app\models;

use app\exception\ChangeStatusException;
use app\exception\UnknownActionException;
use app\exception\WrongRoleException;
use app\models\actions\CancelAction;
use app\models\actions\CompleteAction;
use app\models\actions\ProposeAction;
use app\models\actions\RefuseAction;
use app\models\actions\StartAction;

class Task
{
    const STATUS_NEW = 'new';
    const STATUS_PROCESS = 'process';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELED = 'canceled';

    private $executor_id;
    private $customer_id;
    private $status;

    private $id;
    private $title;
    private $description;
    private $creation_time;
    private $url_file;
    private $deadline;
    private $budget;
    private $category;
    private $location_id;

    public function loadCsvArray(array $array): void
    {
        $this->id = $array['task_number'];
        $this->title = $array['task_title'];
        $this->description = $array['task_description'];
        $this->creation_time = $array['task_dt_add'];
        $this->url_file = $array['url_file'];
        $this->deadline = $array['task_deadline'];
        $this->budget = $array['task_budget'];
        $this->category = $array['task_category'];
        $this->location_id = $array['task_location'];
        $this->customer_id = $array['task_customer'];
        $this->executor_id = $array['task_executor'];
        $this->status = $array['task_status'];
    }

    public function getAttributes()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'creation_time' => $this->creation_time,
            'url_file' => $this->url_file,
            'deadline' => $this->deadline,
            'budget' => $this-> budget,
            'category' => $this->category,
            'location' => $this->location_id,
            'customer' => $this->customer_id,
            'executor' => $this->executor_id,
            'status' => $this->status
        ];
    }

    public function __construct(int $customer_id)
    {
        $this->customer_id = $customer_id;
        $this->status = self::STATUS_NEW;
    }

    public function listActs (): array
    {
        return $acts = [CancelAction::class, CompleteAction::class, RefuseAction::class, StartAction::class];
    }

    public function listStatus (): array
    {
        return $status = [self::STATUS_CANCELED, self::STATUS_NEW, self::STATUS_COMPLETED, self::STATUS_PROCESS, self::STATUS_FAILED];
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCustomer(): int
    {
        return $this->customer_id;
    }

    public function getExecutor(): ?int
    {
        return $this->executor_id;
    }

    /**
     * @param int $initiator_id
     * @throws ChangeStatusException
     * @throws WrongRoleException
     */
    public function complete(int $initiator_id) {
            if (!CompleteAction::verifyAbility($initiator_id, $this)) {
                throw new ChangeStatusException('Ошибка при выставлении статуса complete');
            }
        $this->status = self::STATUS_COMPLETED;
        }


    /**
     * @param int $initiator_id
     * @throws ChangeStatusException
     * @throws WrongRoleException
     */
    public function cancel(int $initiator_id) {
        if (!CancelAction::verifyAbility($initiator_id, $this)) {
            throw new ChangeStatusException('Ошибка при выставлении статуса cancel');
        }
        $this->status = self::STATUS_CANCELED;
    }

    /**
     * @param int $initiator_id
     * @throws ChangeStatusException
     * @throws WrongRoleException
     */
    public function refuse(int $initiator_id) {
        if (!RefuseAction::verifyAbility($initiator_id, $this)) {
            throw new ChangeStatusException('Ошибка при выставлении статуса refuse');
        }
        $this->status = self::STATUS_FAILED;
    }

    /**
     * @param int $initiator_id
     * @throws ChangeStatusException
     * @throws WrongRoleException
     */
    public function start(int $initiator_id) {
        if (!StartAction::verifyAbility($initiator_id, $this)) {
            throw new ChangeStatusException('Ошибка при выставлении статуса start');
        }
        $this->status = self::STATUS_PROCESS;
    }

    /**
     * @param int $initiator_id
     * @throws ChangeStatusException
     * @throws WrongRoleException
     */
    public function propose(int $initiator_id) {
        if (!ProposeAction::verifyAbility($initiator_id, $this)) {
            throw new ChangeStatusException('Ошибка при выставлении статуса propose');
        }
    }

    /**
     * @param int $initiator_id
     * @return array
     * @throws WrongRoleException
     */
    public function getAvailableActions(int $initiator_id): array
    {
        $result = [];

        if (StartAction::verifyAbility($initiator_id, $this)) {
            $result[] = StartAction::getName();
        }

        if (CompleteAction::verifyAbility($initiator_id, $this)) {
            $result[] = CompleteAction::getName();
        }

        if (RefuseAction::verifyAbility($initiator_id, $this)) {
            $result[] = RefuseAction::getName();
        }

        if (CancelAction::verifyAbility($initiator_id, $this)) {
            $result[] = CancelAction::getName();
        }

        if (ProposeAction::verifyAbility($initiator_id, $this)) {
            $result[] = ProposeAction::getName();
        }
        return $result;
    }

    public function setExecutor(int $executor_id)
    {
        $this->executor_id = $executor_id;
    }

    public function setCustomer(int $customer_id)
    {
        $this->customer_id = $customer_id;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    /**
     * @param $action
     * @return string
     * @throws UnknownActionException
     */
    public function getNewStatus ($action): string
    {
        switch ($action) {
            case StartAction::getName():
                return self::STATUS_PROCESS;

            case CompleteAction::getName():
                return self::STATUS_COMPLETED;

            case CancelAction::getName():
                return self::STATUS_CANCELED;

            case RefuseAction::getName():
                return self::STATUS_FAILED;
        }
        throw new UnknownActionException('Неизвестное действие');
    }
}
