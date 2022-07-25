<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;


class InviteService
{


    private $em;
    private $translator;
    private $router;
    private $mailer;
    private $parameterBag;
    private $twig;

    public function __construct(Environment $environment, ParameterBagInterface $parameterBag, MailerService $mailerService, EntityManagerInterface $entityManager, TranslatorInterface $translator, UrlGeneratorInterface $urlGenerator)
    {
        $this->translator = $translator;
        $this->em = $entityManager;
        $this->router = $urlGenerator;
        $this->mailer = $mailerService;
        $this->parameterBag = $parameterBag;
        $this->twig = $environment;
    }

    public function newUser($email)
    {
        $user = $this->em->getRepository(User::class)->findOneBy(array('email' => $email));
        if (!$user) {
            $user = new User();
            $user->setLastName('')
                ->setFirstName('')
                ->setCreatedAt(new \DateTime())
                ->setRegisterId(md5(uniqid('ksdjhfkhsdkjhjksd', true)))
                ->setUsername($email)
                ->setEmail($email)
                ->setPassword('123')
                ->setUuid('123');
            $user->setEmail($email);
        }

        $user->setRegisterId(md5(uniqid('ksdjhfkhsdkjhjksd', true)));
        $this->em->persist($user);
        $this->em->flush();
        $link = $this->router->generate('invitation_accept', array('id' => $user->getRegisterId()), UrlGeneratorInterface::ABSOLUTE_URL);
        $content = $this->twig->render('email/addUser/resetting_html.html.twig', ['link' => $link]);
        $this->mailer->sendEmail(
            $this->parameterBag->get('defaultEmail'),
            $this->parameterBag->get('defaultEmail'),
            $email,
            $this->translator->trans('Einladung zum ODC'),
            $content);
        return $user;
    }

    public function connectUserWithEmail(User $userfromregisterId, User $user)
    {
        if ($user !== $userfromregisterId) {
            if (!$user->getTeams()) {
                $user->setTeams($userfromregisterId->getTeams());
            }
            if (!$user->getAkademieUser()) {
                $user->setAkademieUser($userfromregisterId->getAkademieUser());
            }
            foreach ($user->getTeamDsb() as $data) {
                $user->addTeamDsb($data);
            }
            $this->em->remove($userfromregisterId);
        }

        $user->setRegisterId(null);
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

}
