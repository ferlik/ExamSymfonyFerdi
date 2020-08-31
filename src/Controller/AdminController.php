<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\String\Slugger\SluggerInterface;

class AdminController extends AbstractController
{

    /**
     * @Route("/admin/new", name="article_new")
     */
    public function addArticle(Request $request, SluggerInterface $slugger)
    {
        $form = $this->createForm(ArticleType::class, new Article());
        $form->handleRequest($request);
        $articles = $form->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $image */
            $image = $form->get('photo')->getData();

            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();
                try {
                    $image->move(
                        'images/upload',
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $articles->setPhoto($newFilename);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($articles);
            $em->flush();
            return $this->redirectToRoute('list_articlePro');
        } else {
            return $this->render('admin/new.html.twig', [
                'form' => $form->createView(),
                // 'errors' => $form->getErrors()
            ]);
        }
    }

    /**
     * @Route("/admin/detail/{id}", name="article_detailPro")
     */
    public function getOne($id)
    {
        $article = $this->getDoctrine()->getRepository(Article::class)->find($id);

        return $this->render('admin/detail.html.twig', [
            'article' => $article,
        ]);
    }
    /**
     * @Route("/admin", name="list_articlePro")
     */
    public function index(ArticleRepository $articleRepository)
    {
        $articles = $articleRepository->findAll();
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/article/delete/{article}", name="article_delete")
     */
    public function delete(Article $article)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($article);
        $entityManager->flush();

        return $this->redirectToRoute('list_articlePro');
    }
}
