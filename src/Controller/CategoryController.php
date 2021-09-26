<?php

namespace App\Controller;

use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{

    /**
     * @Route("/data/categories", name="categories")
     */
    public function details(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_IMAGE_UPLOADER', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(Category::class);

        $categories = $repository->fetchParents('representatives' == $request->query->get('type', false));

        $result = [];

        foreach ($categories as $category) {
            $result[] = [
                'id'   => $category->getId(),
                'name' => $category->getName()
            ];
            foreach ($category->getChildred() as $child) {
                $result[] = [
                    'id'     => $child->getId(),
                    'name'   => $child->getName(),
                    'parent' => $category->getId()
                ];
            }
        }

        return $this->json($result);
    }
}
