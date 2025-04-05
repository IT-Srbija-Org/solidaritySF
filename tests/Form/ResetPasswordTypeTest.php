<?php

namespace App\Tests\Form;

use App\Form\ResetPasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Test\TypeTestCase;

class ResetPasswordTypeTest extends TypeTestCase
{
    public function testResetPasswordFormHasCorrectFields(): void
    {
        $form = $this->factory->create(ResetPasswordType::class);
        
        // Check that form contains the expected fields
        $this->assertTrue($form->has('email'));
        
        // Get the form field types
        $this->assertInstanceOf(EmailType::class, $form->get('email')->getConfig()->getType()->getInnerType());
        
        // Test field options
        $emailField = $form->get('email');
        $this->assertEquals('Email', $emailField->getConfig()->getOption('label'));
    }
    
    public function testSubmitValidData(): void
    {
        $formData = [
            'email' => 'user@example.com',
        ];
        
        $form = $this->factory->create(ResetPasswordType::class);
        
        // Submit the form with test data
        $form->submit($formData);
        
        $this->assertTrue($form->isSynchronized());
        
        // Check that the form data was correctly bound
        $formData = $form->getData();
        $this->assertEquals('user@example.com', $formData['email']);
    }
}