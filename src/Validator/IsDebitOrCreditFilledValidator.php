<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IsDebitOrCreditFilledValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        $form = $this->context->getRoot();
        $debit = $form->get('debit')->getData();
        $credit = $form->get('credit')->getData();

        if (!$constraint instanceof IsDebitOrCreditFilled) {
            throw new UnexpectedTypeException($constraint, IsDebitOrCreditFilled::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (
            ($debit === null && $credit === null) || // Si aucun renseigné
            ($debit !== null && $credit !== null)    // Si les deux renseignés
        ) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('debit')
                ->atPath('credit')
                ->addViolation()
            ;
        }
    }
}
