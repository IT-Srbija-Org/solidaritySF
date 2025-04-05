<?php

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\ProfileChangePasswordType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Test\TypeTestCase;

class ProfileChangePasswordTypeTest extends TypeTestCase
{
    public function testChangePasswordFormHasCorrectFields(): void
    {
        $form = $this->factory->create(ProfileChangePasswordType::class);
        
        // Check that form contains the expected fields
        $this->assertTrue($form->has('currentRawPassword'));
        $this->assertTrue($form->has('rawPassword'));
        $this->assertTrue($form->has('submit'));
        
        // Get the form field types
        $this->assertInstanceOf(PasswordType::class, $form->get('currentRawPassword')->getConfig()->getType()->getInnerType());
        $this->assertInstanceOf(RepeatedType::class, $form->get('rawPassword')->getConfig()->getType()->getInnerType());
        $this->assertInstanceOf(SubmitType::class, $form->get('submit')->getConfig()->getType()->getInnerType());
    }
    
    public function testSubmitValidData(): void
    {
        $formData = [
            'currentRawPassword' => 'old_password',
            'rawPassword' => [
                'first' => 'new_password',
                'second' => 'new_password',
            ],
        ];
        
        $user = new User();
        $form = $this->factory->create(ProfileChangePasswordType::class, $user);
        
        // Submit the form with test data
        $form->submit($formData);
        
        $this->assertTrue($form->isSynchronized());
        
        // Check that the form data was mapped to the entity
        $this->assertEquals('old_password', $user->getCurrentRawPassword());
        $this->assertEquals('new_password', $user->getRawPassword());
    }
    
    public function testConfigureOptions(): void
    {
        $form = $this->factory->create(ProfileChangePasswordType::class);
        $options = $form->getConfig()->getOptions();
        
        // Test that data_class is set to User::class
        $this->assertEquals(User::class, $options['data_class']);
        
        // Test that validation_groups are set correctly
        $this->assertContains('currentRawPassword', $options['validation_groups']);
        $this->assertContains('rawPassword', $options['validation_groups']);
    }
}