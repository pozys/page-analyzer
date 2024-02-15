<?php

declare(strict_types=1);

namespace Pozys\PageAnalyzer\Services;

use Valitron\Validator;

class ValidationService
{
    private Validator $validator;

    public function __construct(private array $data)
    {
        $validator = new Validator($data);
        $validator->setPrependLabels(false);
        $this->validator = $validator;
    }

    public function validateUrl(): array
    {
        $this->validator
            ->rule('required', 'name')->message('URL не должен быть пустым')
            ->rule('lengthMax', 'name', 255)->message('URL не должен быть длиннее 255 символов')
            ->rule('url', 'name')->message('Некорректный URL');

        $this->validator->validate();

        return $this->validator->errors();
    }
}
