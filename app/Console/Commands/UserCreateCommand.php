<?php

declare(strict_types=1);

namespace Morgo\Console\Commands;

use Morgo\Domain\User\User;
use Morgo\Domain\User\UserRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class UserCreateCommand extends Command
{
    protected static $defaultName = 'user:create';

    public function __construct(private readonly UserRepositoryInterface $users)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Interaktivně vytvoří nového uživatele.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $email = $helper->ask($input, $output, new Question('E-mail: '));
        $name  = $helper->ask($input, $output, new Question('Jméno: '));
        $passQ = new Question('Heslo (min. 8 znaků): ');
        $passQ->setHidden(true);
        $pass  = $helper->ask($input, $output, $passQ);

        if (!$email || !$pass || strlen($pass) < 8) {
            $output->writeln('<error>Neplatný vstup.</error>');
            return Command::FAILURE;
        }

        $user                = new User();
        $user->email         = strtolower(trim($email));
        $user->display_name  = $name ?: $email;
        $user->password_hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
        $user->role          = 'admin';

        $this->users->save($user);
        $output->writeln("<info>Uživatel {$user->email} vytvořen (admin).</info>");
        return Command::SUCCESS;
    }
}
