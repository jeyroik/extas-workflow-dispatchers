<?php
namespace extas\components\workflows\transitions\dispatchers;

use extas\components\plugins\Plugin;
use extas\interfaces\IItem;
use extas\interfaces\workflows\entities\IWorkflowEntity;
use extas\interfaces\workflows\schemas\IWorkflowSchema;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherExecutor;
use extas\interfaces\workflows\transitions\errors\ITransitionErrorVocabulary;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use extas\interfaces\workflows\transitions\results\ITransitionResult;

/**
 * Class ConditionDateTime
 *
 * @package extas\components\plugins\workflows\conditions
 * @author jeyroik@gmail.com
 */
class DateTime extends Plugin implements ITransitionDispatcherExecutor
{
    /**
     * @param ITransitionDispatcher $dispatcher
     * @param IWorkflowTransition $transition
     * @param IWorkflowEntity $entitySource
     * @param IWorkflowSchema $schema
     * @param IItem $context
     * @param ITransitionResult $result
     * @param IWorkflowEntity $entityEdited
     *
     * @return bool
     */
    public function __invoke(
        ITransitionDispatcher $dispatcher,
        IWorkflowTransition $transition,
        IWorkflowEntity $entitySource,
        IWorkflowSchema $schema,
        IItem $context,
        ITransitionResult &$result,
        IWorkflowEntity &$entityEdited
    ): bool
    {
        $datetime = $dispatcher->getParameter('datetime');

        if ($datetime) {
            $compare = $dispatcher->getParameter('compare');
            if ($compare) {
                $compareCondition = $compare->getValue();
                if (method_exists($this, $compareCondition . 'Compare')) {
                    $conditionValue = is_numeric($datetime->getValue())
                        ? $datetime->getValue()
                        : strtotime($datetime->getValue());

                    $valid = $this->{$compareCondition . 'Compare'}(time(), $conditionValue);

                    if (!$valid) {
                        $result->fail(ITransitionErrorVocabulary::ERROR__VALIDATION_FAILED, [
                            'datetime' => 'Not valid datetime `' . $conditionValue . '`'
                        ]);
                    }

                    return $valid;
                }
            }
        }

        $result->fail(ITransitionErrorVocabulary::ERROR__VALIDATION_FAILED, [
            'datetime' => 'Missed datetime parameter in the dispatcher `' . $dispatcher->getName() . '`'
        ]);

        return false;
    }

    /**
     * @param $entityValue
     * @param $conditionValue
     *
     * @return bool
     */
    protected function notEqualCompare($entityValue, $conditionValue): bool
    {
        return $entityValue != $conditionValue;
    }

    /**
     * @param $entityValue
     * @param $conditionValue
     *
     * @return bool
     */
    protected function greaterCompare($entityValue, $conditionValue): bool
    {
        return $entityValue > $conditionValue;
    }

    /**
     * @param $entityValue
     * @param $conditionValue
     *
     * @return bool
     */
    protected function lowerCompare($entityValue, $conditionValue): bool
    {
        return $entityValue < $conditionValue;
    }
}
