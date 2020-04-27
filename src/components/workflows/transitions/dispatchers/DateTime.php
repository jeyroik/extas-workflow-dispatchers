<?php
namespace extas\components\workflows\transitions\dispatchers;

use extas\components\conditions\ConditionParameter;
use extas\components\conditions\THasCondition;
use extas\components\errors\Error;
use extas\components\Item;
use extas\components\THasValue;
use extas\interfaces\conditions\IHasCondition;
use extas\interfaces\workflows\entities\IEntity;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherExecutor;
use extas\interfaces\workflows\transits\ITransitResult;


/**
 * Class ConditionDateTime
 *
 * @package extas\components\plugins\workflows\conditions
 * @author jeyroik@gmail.com
 */
class DateTime extends TransitionDispatcherExecutor implements ITransitionDispatcherExecutor
{
    /**
     * @param ITransitResult $result
     * @param IEntity $entityEdited
     * @return bool
     * @throws
     */
    public function __invoke(ITransitResult &$result, IEntity &$entityEdited): bool
    {
        $datetime = $this->getParameterValue('datetime');
        $condition = $this->getParameterValue('compare');

        $withCondition = new ConditionParameter([
            IHasCondition::FIELD__VALUE => $datetime,
            IHasCondition::FIELD__CONDITION => $condition
        ]);

        $validationSuccess = true;
        if (!$withCondition->isConditionTrue(time())) {
            $validationSuccess = false;
            $result->addError(new Error([
                Error::FIELD__NAME => 'incorrect_datetime',
                Error::FIELD__TITLE => 'Incorrect datetime',
                Error::FIELD__DESCRIPTION => 'Incorrect datetime',
                Error::FIELD__CODE => 400
            ]));
        }

        return $validationSuccess;
    }
}
