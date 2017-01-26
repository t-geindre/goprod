<?php

namespace ApiBundle\Form\Serializer;

use Symfony\Component\Form\Form;

class ErrorSerializer
{
    public function serializeErrors(Form $form)
    {
        $errors = [
            'global' => [],
            'fields' => [],
        ];

        foreach ($form->getErrors() as $error) {
            $errors['global'][] = $error->getMessage();
        }

        $errors['fields'] = $this->serialize($form);

        return $errors;
    }

    private function serialize(\Symfony\Component\Form\Form $form)
    {
        $errors = [];
        foreach ($form->getIterator() as $key => $child) {

            foreach ($child->getErrors() as $error){
                $errors[$key] = $error->getMessage();
            }

            if (count($child->getIterator()) > 0) {
                $errors[$key] = $this->serialize($child);
            }
        }

        return $errors;
    }
}
