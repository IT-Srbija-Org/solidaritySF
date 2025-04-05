<?php

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\ChangePasswordType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Test\TypeTestCase;

class ChangePasswordTypeTest extends TypeTestCase
{
    public function testChangePasswordFormHasCorrectFields(): void
    {
        $form = $this->factory->create(ChangePasswordType::class);
        
        // Check that form contains the expected fields
        $this->assertTrue($form->has('rawPassword'));
        
        // Get the form field types
        $this->assertInstanceOf(RepeatedType::class, $form->get('rawPassword')->getConfig()->getType()->getInnerType());
        
        // Test field options
        $rawPasswordField = $form->get('rawPassword');
        $this->assertEquals(PasswordType::class, $rawPasswordField->getConfig()->getOption('type'));
        $this->assertEquals('Lozinke se ne podudaraju.', $rawPasswordField->getConfig()->getOption('invalid_message'));
        
        // Test first and second options
        $firstOptions = $rawPasswordField->getConfig()->getOption('first_options');
        $secondOptions = $rawPasswordField->getConfig()->getOption('second_options');
        
        $this->assertEquals('Nova lozinka', $firstOptions['attr']['placeholder']);
        $this->assertEquals('Ponovite novu lozinku', $secondOptions['attr']['placeholder']);
    }
    
    public function testSubmitValidData(): void
    {
        $formData = [
            'rawPassword' => [
                'first' => 'new_password',
                'second' => 'new_password',
            ],
        ];
        
        $user = new User();
        $form = $this->factory->create(ChangePasswordType::class, $user);
        
        // Submit the form with test data
        $form->submit($formData);
        
        $this->assertTrue($form->isSynchronized());
        
        // Check that the form data was mapped to the entity
        $this->assertEquals('new_password', $user->getRawPassword());
    }
    
    public function testConfigureOptions(): void
    {
        $form = $this->factory->create(ChangePasswordType::class);
        $options = $form->getConfig()->getOptions();
        
        // Test that data_class is set to User::class
        $this->assertEquals(User::class, $options['data_class']);
        
        // Test that validation_groups are set correctly
        $this->assertContains('rawPassword', $options['validation_groups']);
    }
}