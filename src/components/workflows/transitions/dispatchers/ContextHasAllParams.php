<?php
namespace extas\components\workflows\transitions\dispatchers;

use extas\components\errors\Error;
use extas\interfaces\workflows\entities\IEntity;
use extas\interfaces\workflows\transits\ITransitResult;

/**
 * Class ValidatorContextHasAllParams
 *
 * @package extas\components\plugins\workflows\validators
 * @author jeyroik@gmail.com
 */
class ContextHasAllParams extends TransitionDispatcherExecutor
{
    /**
     * @param ITransitResult $result
     * @param IEntity $entityEdited
     * @return bool
     */
    public function __invoke(ITransitResult &$result, IEntity &$entityEdited): bool
    {
        $requiredParamsNames = $this->getParametersNames();
        $context = $this->getContext();
        $validationSuccess = true;

        if (!$context->has(...$requiredParamsNames)) {
            $result->addError(new Error([
                Error::FIELD__NAME => 'missed_param',
                Error::FIELD__TITLE => 'Missed param',
                Error::FIELD__DESCRIPTION => $this->getErrorDescription($requiredParamsNames),
                Error::FIELD__CODE => 400
            ]));
            $validationSuccess = false;
        }

        return $validationSuccess;
    }

    /**
     * @param array $names
     * @return string
     */
    protected function getErrorDescription(array $names): string
    {
        return 'Can not find one of params "' . implode('", "', $names) . '" in a context';
    }
}
