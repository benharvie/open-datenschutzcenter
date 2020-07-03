<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */

namespace App\Controller;

use App\Entity\AkademieBuchungen;
use App\Entity\AuditTom;
use App\Entity\Datenweitergabe;
use App\Entity\Forms;
use App\Entity\Kontakte;
use App\Entity\Tom;
use App\Entity\VVT;
use App\Entity\VVTDsfa;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="dashboard")
     */
    public function dashboard()
    {
        $team = $this->getUser()->getTeam();

        if ($team === null && $this->getUser()->getAkademieUser() !== null) {
            return $this->redirectToRoute('akademie');
        } elseif ($team === null && $this->getUser()->getAkademieUser() === null) {
            return $this->redirectToRoute('fos_user_security_logout');
        }

        $audit = $this->getDoctrine()->getRepository(AuditTom::class)->findAllByTeam($team);
        $daten = $this->getDoctrine()->getRepository(Datenweitergabe::class)->findBy(array('team'=>$team,'activ'=>true,'art'=>1));
        $av = $this->getDoctrine()->getRepository(Datenweitergabe::class)->findBy(array('team'=>$team,'activ'=>true,'art'=>2));
        $vvt = $this->getDoctrine()->getRepository(VVT::class)->findActivByTeam($team);
        $vvtDsfa = $this->getDoctrine()->getRepository(VVTDsfa::class)->findActivByTeam($team);
        $kontakte = $this->getDoctrine()->getRepository(Kontakte::class)->findActivByTeam($team);
        $tom = $this->getDoctrine()->getRepository(Tom::class)->findActivByTeam($team);
        $forms = $this->getDoctrine()->getRepository(Forms::class)->findActivByTeam($team);

        $qb = $this->getDoctrine()->getRepository(AuditTom::class)->createQueryBuilder('audit');
        $qb->andWhere('audit.team = :team')
            ->andWhere('audit.activ = 1')
            ->andWhere('audit.status = 5 OR audit.status = 6')
            ->orderBy('audit.createdAt', 'DESC')
            ->setParameter('team', $team);
        $query = $qb->getQuery();
        $kristischeAudits = $query->getResult();

        $qb = $this->getDoctrine()->getRepository(VVT::class)->createQueryBuilder('vvt');
        $qb->andWhere('vvt.team = :team')
            ->andWhere('vvt.activ = 1')
            ->andWhere('vvt.status = 3')
            ->orderBy('vvt.CreatedAt', 'DESC')
            ->setParameter('team', $team);
        $query = $qb->getQuery();
        $kristischeVvts = $query->getResult();

        $qb = $this->getDoctrine()->getRepository(VVTDsfa::class)->createQueryBuilder('dsfa');
        $qb->innerJoin('dsfa.vvt', 'vvt')
            ->andWhere('vvt.activ = 1')
            ->andWhere('dsfa.activ = 1')
            ->andWhere('dsfa.dsb IS NULL OR dsfa.ergebnis IS NULL')
            ->andWhere('vvt.team = :team')
            ->setParameter('team', $team);
        $query = $qb->getQuery();
        $openDsfa = $query->getResult();

        $assignVvt = $this->getUser()->getAssignedVvts()->toarray();
        $assignAudit = $this->getUser()->getAssignedAudits()->toarray();
        $assignDsfa = $this->getUser()->getAssignedDsfa()->toarray();
        $assignDatenweitergabe = $this->getUser()->getAssignedDatenweitergaben()->toarray();

        $buchungen = $this->getDoctrine()->getRepository(AkademieBuchungen::class)->findActivBuchungenByUser($this->getUser());

        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'team' => $team,
            'audit' => $audit,
            'daten' => $daten,
            'vvt' => $vvt,
            'dsfa' => $vvtDsfa,
            'kontakte' => $kontakte,
            'kAudit' => $kristischeAudits,
            'kVvt' => $kristischeVvts,
            'openDsfa' => $openDsfa,
            'tom' => $tom,
            'av' => $av,
            'assignDaten' => $assignDatenweitergabe,
            'assignVvt' => $assignVvt,
            'assignAudit' => $assignAudit,
            'assignDsfa' => $assignDsfa,
            'akademie' => $buchungen,
            'forms' => $forms
        ]);
    }
}
