<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\ORM\EntityManagerInterface;

class EventCrudController extends AbstractCrudController
{
    public function __construct(private readonly SluggerInterface $slugger) {

    }

    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('title')->setRequired(true);
        yield TextField::new('slug')->onlyOnIndex();
        yield TextField::new('description')->setRequired(true);
        yield DateField::new('start_at')->setRequired(true);
        yield DateField::new('end_at')->setRequired(true);
        yield IntegerField::new('capacity')->setRequired(true);
        yield ChoiceField::new('status')->setRequired(true);
        yield AssociationField::new('organizer')
            ->setRequired(true)
            ->setFormTypeOption(
                'query_builder',
                function (UserRepository $userRepository) {
                    return $userRepository
                        ->createQueryBuilder('u')
                        ->where('u.roles LIKE :admin')
                        ->orWhere('u.roles LIKE :organiser')
                        ->setParameter('admin', '%ROLE_ADMIN%')
                        ->setParameter('organiser', '%ROLE_ORGANISER%');
                }
            );
        yield AssociationField::new('category')->setRequired(true);
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

        if (!$entityInstance instanceof Event) {
            return;
        }

        $slug = $this->slugger
            ->slug($entityInstance->getTitle())
            ->lower();

        $entityInstance->setSlug(
            $slug->toString()
        );

        parent::persistEntity(
            $entityManager,
            $entityInstance
        );
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void {

        if (!$entityInstance instanceof Event) {
            return;
        }

        $slug = $this->slugger
            ->slug($entityInstance->getTitle())
            ->lower();

        $entityInstance->setSlug(
            $slug->toString()
        );

        $this->addFlash(
            'success',
            'Événement mis à jour avec succès.'
        );

        parent::updateEntity(
            $entityManager,
            $entityInstance
        );
    }
}
