<?php
namespace Aijko\StoreLocator\Task\Store;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Julian Kleinhans <julian.kleinhans@aijko.de>, aijko GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class ImportTaskAddFields
 *
 * @package Aijko\StoreLocator\Task\Store
 */
class ImportTaskAddFields implements \TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface {

	/**
	 * Gets additional fields to render in the form to add/edit a task
	 *
	 * @param array $taskInfo
	 * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule
	 * @return array
	 */
	public function getAdditionalFields(array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule) {
		$additionalFields = array();

		if (empty($taskInfo['storagePid'])) {
			if ($schedulerModule->CMD == 'add') {
				$taskInfo['storagePid'] = 0;
				$task->storagePid = 0;
			} elseif ($schedulerModule->CMD == 'edit') {
				$taskInfo['storagePid'] = $task->storagePid;
			} else {
				$taskInfo['storagePid'] = $task->storagePid;
			}
		}

		if (empty($taskInfo['csvPath'])) {
			if ($schedulerModule->CMD == 'add') {
				$taskInfo['csvPath'] = array();
			} elseif ($schedulerModule->CMD == 'edit') {
				$taskInfo['csvPath'] = $task->csvPath;
			} else {
				$taskInfo['csvPath'] = $task->csvPath;
			}
		}

		if (empty($taskInfo['truncate'])) {
			if ($schedulerModule->CMD == 'add') {
				$taskInfo['truncate'] = array();
			} elseif ($schedulerModule->CMD == 'edit') {
				$taskInfo['truncate'] = $task->truncate;
			} else {
				$taskInfo['truncate'] = $task->truncate;
			}
		}

		// input for storagePid
		$fieldId = 'task_storagePid';
		$fieldCode = '<input name="tx_scheduler[storagePid]" type="text" id="' . $fieldId . '" value="' . $task->storagePid . '" />';
		$additionalFields[$fieldId] = array(
			'code' => $fieldCode,
			'label' => 'Storage Pid'
		);

		// input for csvPath
		$fieldId = 'task_csvPath';
		$fieldCode = '<input style="width:600px" name="tx_scheduler[csvPath]" type="text" id="' . $fieldId . '" value="' . $task->csvPath . '" />';
		$additionalFields[$fieldId] = array(
			'code' => $fieldCode,
			'label' => 'Relative CSV file path'
		);

		// Truncate
		$fieldId = 'task_truncate';
		$fieldCode = '<input name="tx_scheduler[truncate]" type="checkbox" id="' . $fieldId . '" ' . ($task->truncate ? 'checked="checked"' : '') . ' value="1" />';
		$additionalFields[$fieldId] = array(
			'code' => $fieldCode,
			'label' => 'Delete all available stores before import'
		);

		return $additionalFields;
	}

	/**
	 * Validates the additional fields' values
	 *
	 * @param array $submittedData
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule
	 * @return bool
	 */
	public function validateAdditionalFields(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule) {
		$isValid = TRUE;

		if (!\TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($submittedData['storagePid']) || $submittedData['storagePid'] < 0) {
			$isValid = FALSE;
			$schedulerModule->addMessage('You must set a storage pid', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
		}

		if (!$submittedData['csvPath']) {
			$isValid = FALSE;
			$schedulerModule->addMessage('You must define a relative path to the csv file', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
		}

		return $isValid;
	}

	/**
	 * Takes care of saving the additional fields' values in the task's object
	 *
	 * @param array $submittedData
	 * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task
	 * @return void
	 */
	public function saveAdditionalFields(array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task) {
		$task->storagePid = intval($submittedData['storagePid']);
		$task->csvPath = $submittedData['csvPath'];
		$task->truncate = $submittedData['truncate'];
	}

}
