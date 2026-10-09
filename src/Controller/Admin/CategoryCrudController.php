<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoryCrudController extends AbstractCrudController
{
    public function __construct(private readonly SluggerInterface $slugger) {}
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('name');
        yield TextField::new('slug')->hideOnForm()->onlyOnIndex();
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

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof Event) {

            $entityInstance->setSlug(
                $this->slugger
                    ->slug($entityInstance->getTitle())
                    ->lower()
                    ->toString()
            );
        }

        parent::persistEntity(
            $entityManager,
            $entityInstance
        );
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof Event) {

            $entityInstance->setSlug(
                $this->slugger
                    ->slug($entityInstance->getTitle())
                    ->lower()
                    ->toString()
            );

            $this->addFlash(
                'success',
                'Évènement modifié avec succès.'
            );
        }

        parent::updateEntity(
            $entityManager,
            $entityInstance
        );
    }
}
