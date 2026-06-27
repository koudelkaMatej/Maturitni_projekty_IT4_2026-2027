<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

require_once __DIR__ . "/model/TicketsApplication.php";

require_once __DIR__ . "/configuration/Configuration.php";

require_once __DIR__ . "/repository/Repository.php";
require_once __DIR__ . "/repository/AssignmentRepository.php";
require_once __DIR__ . "/repository/AutoAssignRepository.php";
require_once __DIR__ . "/repository/CategoryRepository.php";
require_once __DIR__ . "/repository/PriorityRepository.php";
require_once __DIR__ . "/repository/RoomRepository.php";
require_once __DIR__ . "/repository/SessionRepository.php";
require_once __DIR__ . "/repository/TeacherRepository.php";
require_once __DIR__ . "/repository/TicketRepository.php";
require_once __DIR__ . "/repository/UserRepository.php";
require_once __DIR__ . "/repository/WorkRepository.php";

require_once __DIR__ . "/model/Ticket.php";
require_once __DIR__ . "/model/User.php";
require_once __DIR__ . "/model/Assignment.php";
require_once __DIR__ . "/model/Work.php";
require_once __DIR__ . "/model/Category.php";
require_once __DIR__ . "/model/Room.php";
require_once __DIR__ . "/model/Priority.php";
require_once __DIR__ . "/model/Teacher.php";
require_once __DIR__ . "/model/PaginatedResult.php";

require_once __DIR__ . "/source/Database.php";
require_once __DIR__ . "/source/Migrator.php";

class SPSTickets implements TicketsApplication
{
    private Configuration $configuration;

    private Database $database;

    private AssignmentRepository $assignmentRepository;
    private AutoAssignRepository $autoAssignRepository;
    private CategoryRepository $categoryRepository;
    private PriorityRepository $priorityRepository;
    private RoomRepository $roomRepository;
    private SessionRepository $sessionRepository;
    private TeacherRepository $teacherRepository;
    private TicketRepository $ticketRepository;
    private UserRepository $userRepository;
    private WorkRepository $workRepository;

    private ?User $currentUser = null;

    private string $currentPage = "Nepojmenovaná stránka";

    public function __construct()
    {
        // Get application configuration
        $this->configuration = new Configuration();

        try {
            // Check for dev environment
            if ($this->configuration->development) {
                ini_set("display_errors", "1");
                ini_set("display_startup_errors", "1");
                error_reporting(E_ALL);
            }

            // Init database connection
            $this->database = new Database($this->configuration);

            // Auto-create missing tables
            (new Migrator($this->database))->migrate();

            // Load all repositories
            $this->assignmentRepository = new AssignmentRepository($this->database);
            $this->autoAssignRepository = new AutoAssignRepository($this->database);
            $this->categoryRepository = new CategoryRepository($this->database);
            $this->priorityRepository = new PriorityRepository($this->database);
            $this->roomRepository = new RoomRepository($this->database);
            $this->sessionRepository = new SessionRepository($this->database);
            $this->teacherRepository = new TeacherRepository($this->database);
            $this->ticketRepository = new TicketRepository($this->database);
            $this->userRepository = new UserRepository($this->database);
            $this->workRepository = new WorkRepository($this->database);

            // Set php session and cookie settings
            session_name($this->configuration->sessionCookie);
            session_set_cookie_params([
                "lifetime" => $this->configuration->sessionLifetime,
                "samesite" => "Strict",
            ]);

            // Refresh user session from cookies
            $this->refreshSession();
        } catch (Exception $exception) {
            die("<h1>SPŠ HelpDesk je momentálně nedostupný.</h1>");
        }
    }

    public function getAssignmentRepository(): AssignmentRepository
    {
        return $this->assignmentRepository;
    }

    public function getAutoAssignRepository(): AutoAssignRepository
    {
        return $this->autoAssignRepository;
    }

    public function getCategoryRepository(): CategoryRepository
    {
        return $this->categoryRepository;
    }

    public function getPriorityRepository(): PriorityRepository
    {
        return $this->priorityRepository;
    }

    public function getRoomRepository(): RoomRepository
    {
        return $this->roomRepository;
    }

    public function getSessionRepository(): SessionRepository
    {
        return $this->sessionRepository;
    }

    public function getTeacherRepository(): TeacherRepository
    {
        return $this->teacherRepository;
    }

    public function getTicketRepository(): TicketRepository
    {
        return $this->ticketRepository;
    }

    public function getUserRepository(): UserRepository
    {
        return $this->userRepository;
    }

    public function getWorkRepository(): WorkRepository
    {
        return $this->workRepository;
    }

    private function startSession(): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
            session_regenerate_id(true);
        }
    }

    private function refreshSession(): void
    {
        $this->startSession();
        if (!isset($_SESSION["session"])) return;

        $session_id = $_SESSION["session"];
        $current_session = $this->getSessionRepository()->getSessionById($session_id);

        if (!isset($current_session["session_user"])) return;
        $this->currentUser = $this->getUserRepository()->getUserById($current_session["session_user"]);
    }

    public function getUser(): ?User
    {
        return $this->currentUser;
    }

    public function createUserSession($user_id): void
    {
        if ($this->configuration->singleSession) $this->getSessionRepository()->deleteUserSessions($user_id);

        $session_id = $this->getSessionRepository()->addSession($user_id);
        $this->startSession();
        $_SESSION["session"] = $session_id;
    }

    public function destroySession(): void
    {
        $this->startSession();
        session_destroy();
    }

    public function checkUser(): void
    {
        if (getApplication()->getUser() == null) {
            getApplication()->redirectInternally("login");
        }
    }

    public function redirectInternally($page): void
    {
        header("Location: /" . $page . ".php");
        die();
    }

    public function setPageName(string $page): void
    {
        $this->currentPage = $page;
    }

    public function getPageName(): string
    {
        return $this->currentPage;
    }

    public function getInitials(string $string): string
    {
        $capitals = preg_replace('/[^\p{Lu}]/u', '', $string);
        $source = $capitals ?: $string;
        $result = mb_substr($source, 0, 2);
        return $result ?: '?';
    }
}

$application = new SPSTickets();

function getApplication(): TicketsApplication
{
    global $application;
    return $application;
}