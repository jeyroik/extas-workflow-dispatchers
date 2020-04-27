<?php
namespace extas\components\workflows\transitions\dispatchers;

use extas\components\errors\Error;
use extas\interfaces\workflows\entities\IEntity;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherExecutor;
use extas\interfaces\workflows\transits\ITransitResult;

/**
 * Class ValidatorHasAllParams
 *
 * @package extas\components\plugins\workflows\validators
 * @author jeyroik@gmail.com
 */
class EntityHasAllParams extends TransitionDispatcherExecutor implements ITransitionDispatcherExecutor
{
    /**
     * @param ITransitResult $result
     * @param IEntity $entityEdited
     * @return bool
     */
    public function __invoke(ITransitResult &$result, IEntity &$entityEdited): bool
    {
        $requiredParamsNames = $this->getParametersNames();
        $validationSuccess = true;
        if (!$entityEdited->has(...$requiredParamsNames)) {
            $validationSuccess = false;
            $result->addError(new Error([
                Error::FIELD__NAME => 'missed_entity_param',
                Error::FIELD__TITLE => 'Missed param',
                Error::FIELD__DESCRIPTION => $this->getErrorDescription($requiredParamsNames),
                Error::FIELD__CODE => 400
            ]));
        }

        return $validationSuccess;
    }

    /**
     * @param array $names
     * @return string
     */
    protected function getErrorDescription(array $names): string
    {
        return 'Can not find one of params "' . implode('", "', $names) . '" in the current entity';
    }
}
