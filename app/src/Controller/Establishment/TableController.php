<?php

namespace App\Controller\Establishment;

use App\Entity\Table;
use App\Form\TableType;
use App\Repository\TableRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Turbo\TurboBundle;

#[Route('/establishment/table')]
class TableController extends AbstractController
{
    #[Route('/', name: 'app_table_index')]
    public function index(TableRepository $tableRepository): Response
    {

        $tables = $tableRepository->findBy(['establishment' => $this->getUser()->getEstablishment()]);

        return $this->render('establishment/table/index.html.twig', [
            'tables' => $tables,
        ]);
    }

    #[Route('/create', name: 'app_table_create', methods: ['GET', 'POST'])]
    public function new(Request $request, TableRepository $tableRepository): Response
    {
        $establishment = $this->getUser()->getEstablishment();
        $tables = $establishment->getTables();

        $table = new Table();
        $form = $this->createForm(TableType::class, $table, [
            'action' => $this->generateUrl('app_table_create'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $table->setEstablishment($establishment);
            $tableRepository->add($table, true);

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $this->addFlash('success', 'Table ajoutée !');
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                return $this->render('establishment/stream/tables.stream.html.twig', ['tables' => $tables]);
            }

            return $this->redirectToRoute('app_table_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('establishment/table/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_table_edit')]
    public function edit(Request $request, Table $table, TableRepository $tableRepository): Response
    {
        $establishment = $this->getUser()->getEstablishment();
        $tables = $establishment->getTables();

        $form = $this->createForm(TableType::class, $table, [
            'action' => $this->generateUrl('app_table_edit', ['id' => $table->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tableRepository->add($table, true);

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $this->addFlash('success', 'Table modifiée !');
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                return $this->render('establishment/stream/tables.stream.html.twig', ['tables' => $tables, 'table' => $table]);
            }

            return $this->redirectToRoute('app_table_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('establishment/table/edit.html.twig', [
            'table' => $table,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_table_delete', methods: ['POST'])]
    public function delete(Request $request, Table $table, TableRepository $tableRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$table->getId(), $request->request->get('_token'))) {
            $tableRepository->remove($table, true);
        }

        return $this->redirectToRoute('app_table_index', [], Response::HTTP_SEE_OTHER);
    }
}
