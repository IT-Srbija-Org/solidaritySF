<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class Mod97Validator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Mod97) {
            throw new UnexpectedTypeException($constraint, Mod97::class);
        }

        if (null === $value) {
            return;
        }

        if (!is_string($value) || '' === $value) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();

            return;
        }

        if (!$this->validateAccountNumber($value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    private function validateAccountNumber(string $accountNumber): bool
    {
        $controlNumber = $this->mod97(substr($accountNumber, 0, -2));

        return str_pad($controlNumber, 2, '0', STR_PAD_LEFT) === substr($accountNumber, -2);
    }

    private function mod97(string $accountNumber, int $base = 100): int
    {
        $controlNumber = 0;

        for ($x = strlen($accountNumber) - 1; $x >= 0; --$x) {
            $num = (int) $accountNumber[$x];
            $controlNumber = ($controlNumber + ($base * $num)) % 97;
            $base = ($base * 10) % 97;
        }

        return 98 - $controlNumber;
    }
}
