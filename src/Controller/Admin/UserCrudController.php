<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Enum\RoleUser;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher){}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('username');
        yield TextField::new('email');
        yield TextField::new('password')
            ->onlyOnForms()
            ->onlyWhenCreating();
        yield ChoiceField::new('roles')
            ->setChoices([
                'ROLE_USER' => RoleUser::USER->value,
                'ROLE_ORGANISER' => RoleUser::ORGANISER->value,
                'ROLE_ADMIN' => RoleUser::ADMIN->value
            ])->allowMultipleChoices();
        yield DateTimeField::new('lastLoginAt')->hideOnForm();
        yield DateTimeField::new('createdAt')->hideOnForm();
        yield DateTimeField::new('updatedAt')->hideOnForm();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(
                Crud::PAGE_INDEX,
                Action::DELETE,
                fn (Action $action) => $action
                    ->setHtmlAttributes([
                        'onclick' => "return confirm('Confirmer la suppression ?')"
                    ])
            );
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void {

        if (!$entityInstance instanceof User) {
            return;
        }

        if ($entityInstance->getPassword()) {

            $entityInstance->setPassword(
                $this->passwordHasher->hashPassword(
                    $entityInstance,
                    $entityInstance->getPassword()
                )
            );
        }

        parent::persistEntity(
            $entityManager,
            $entityInstance
        );
    }
}
