<?php
namespace tests;

use extas\components\errors\Error;
use extas\components\Item;
use extas\components\samples\parameters\THasSampleParameters;
use extas\interfaces\IItem;
use extas\interfaces\workflows\entities\IEntity;
use extas\interfaces\workflows\transitions\dispatchers\INotifier;
use extas\interfaces\workflows\transits\ITransitResult;

/**
 * Class NotifierError
 *
 * @package tests
 * @author jeyroik@gmail.com
 */
class NotifierError extends Item implements INotifier
{
    use THasSampleParameters;

    /**
     * @param IEntity $entity
     * @param IItem $context
     * @param ITransitResult $result
     */
    public function notify(IEntity $entity, IItem $context, ITransitResult &$result): void
    {
        $result->addError(new Error([
            Error::FIELD__NAME => 'can not notify',
            Error::FIELD__TITLE => 'This notifier can notify anybody',
            Error::FIELD__CODE => 400
        ]));
    }

    protected function getSubjectForExtension(): string
    {
        return 'test.notifier';
    }
}
