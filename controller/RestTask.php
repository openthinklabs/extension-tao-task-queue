<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoTaskQueue\controller;

use common_session_SessionManager;
use oat\taoTaskQueue\model\TaskLogInterface;
use tao_actions_RestController;

class RestTask extends tao_actions_RestController
{
    const PARAMETER_TASK_ID = 'taskId';
    const PARAMETER_LIMIT = 'limit';
    const PARAMETER_OFFSET = 'offset';

    /** @var string */
    private $userId;

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();

        $this->userId = common_session_SessionManager::getSession()->getUserUri();
    }

    /**
     * @throws \common_exception_NotImplemented
     */
    public function getAll()
    {
        /** @var TaskLogInterface $taskLogService */
        $taskLogService = $this->getServiceManager()->get(TaskLogInterface::SERVICE_ID);
        $limit = $offset = null;

        if ($this->hasRequestParameter(self::PARAMETER_LIMIT)) {
            $limit = (int) $this->getRequestParameter(self::PARAMETER_LIMIT);
        }

        if ($this->hasRequestParameter(self::PARAMETER_OFFSET)) {
            $offset = (int) $this->getRequestParameter(self::PARAMETER_OFFSET);
        }

        $this->returnSuccess($taskLogService->findAvailableByUser($this->userId, $limit, $offset)->jsonSerialize());
    }

    /**
     * @throws \common_exception_NotImplemented
     */
    public function get()
    {
        /** @var TaskLogInterface $taskLogService */
        $taskLogService = $this->getServiceManager()->get(TaskLogInterface::SERVICE_ID);

        try {
            $this->assertTaskIdExists();

            $response = $taskLogService->getByIdAndUser(
                $this->getRequestParameter(self::PARAMETER_TASK_ID),
                $this->userId
            );
            $this->returnSuccess($response->jsonSerialize());

        } catch (\Exception $e) {
            $this->returnFailure($e);
        }
    }

    /**
     * @throws \common_exception_NotImplemented
     */
    public function stats()
    {
        /** @var TaskLogInterface $taskLogService */
        $taskLogService = $this->getServiceManager()->get(TaskLogInterface::SERVICE_ID);

        $this->returnSuccess($taskLogService->getStats($this->userId)->jsonSerialize());
    }

    /**
     * @throws \common_exception_NotImplemented
     */
    public function archive()
    {
        /** @var TaskLogInterface $taskLogService */
        $taskLogService = $this->getServiceManager()->get(TaskLogInterface::SERVICE_ID);

        try{
            $this->assertTaskIdExists();
            $taskLogEntity = $taskLogService->getByIdAndUser($this->getRequestParameter(self::PARAMETER_TASK_ID), $this->userId);

            $this->returnSuccess($taskLogService->archive($taskLogEntity));
        } catch (\Exception $e) {
            $this->returnFailure($e);
        }
    }

    /**
     * @throws \common_exception_MissingParameter
     */
    protected function assertTaskIdExists()
    {
        if (!$this->hasRequestParameter(self::PARAMETER_TASK_ID)) {
            throw new \common_exception_MissingParameter(self::PARAMETER_TASK_ID, $this->getRequestURI());
        }
    }
}