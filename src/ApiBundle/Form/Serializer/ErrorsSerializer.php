<?php

namespace ApiBundle\Form\Serializer;

use Symfony\Component\Form\Form;

/**
 * Form errors serializer
 */
class ErrorsSerializer
{
    /**
     * @param Form $form
     *
     * @return array
     */
    public function serializeErrors(Form $form) : array
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

    /**
     * @param \Symfony\Component\Form\Form $form
     *
     * @return array
     */
    protected function serialize(\Symfony\Component\Form\Form $form) : array
    {
        $errors = [];
        foreach ($form->getIterator() as $key => $child) {
            foreach ($child->getErrors() as $error) {
                $errors[$key] = $error->getMessage();
            }

            if (count($child->getIterator()) > 0) {
                $errors[$key] = $this->serialize($child);
            }
        }

        return $errors;
    }
}
