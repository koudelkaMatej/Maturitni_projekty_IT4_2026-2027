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
require_once __DIR__ . "/repository/TicketEventRepository.php";
require_once __DIR__ . "/repository/UserRepository.php";
require_once __DIR__ . "/repository/WorkRepository.php";
require_once __DIR__ . "/repository/GuestAccessRepository.php";

require_once __DIR__ . "/model/Ticket.php";
require_once __DIR__ . "/model/TicketEvent.php";
require_once __DIR__ . "/model/User.php";
require_once __DIR__ . "/model/GuestAccess.php";
require_once __DIR__ . "/model/Assignment.php";
require_once __DIR__ . "/model/Work.php";
require_once __DIR__ . "/model/Category.php";
require_once __DIR__ . "/model/Room.php";
require_once __DIR__ . "/model/Priority.php";
require_once __DIR__ . "/model/Teacher.php";
require_once __DIR__ . "/model/PaginatedResult.php";

require_once __DIR__ . "/source/Database.php";
require_once __DIR__ . "/source/Migrator.php";

/**
 * Application singleton and service locator.
 *
 * Bootstraps the database connection, runs the auto-migrator, loads
 * every repository, and manages the current user session. Accessed
 * globally via {@see getApplication()}.
 */
class SPSTickets implements TicketsApplication
{
    /** Application configuration. */
    private Configuration $configuration;

    /** PDO wrapper. */
    private Database $database;

    /** Repository for ticket-to-user assignments. */
    private AssignmentRepository $assignmentRepository;

    /** Repository for auto-assignment rules. */
    private AutoAssignRepository $autoAssignRepository;

    /** Repository for ticket categories. */
    private CategoryRepository $categoryRepository;

    /** Repository for ticket priorities. */
    private PriorityRepository $priorityRepository;

    /** Repository for rooms / classrooms. */
    private RoomRepository $roomRepository;

    /** Repository for DB-backed sessions. */
    private SessionRepository $sessionRepository;

    /** Repository for teacher records. */
    private TeacherRepository $teacherRepository;

    /** Repository for ticket CRUD and statistics. */
    private TicketRepository $ticketRepository;

    /** Repository for the ticket activity log. */
    private TicketEventRepository $ticketEventRepository;

    /** Repository for user (technician) accounts. */
    private UserRepository $userRepository;

    /** Repository for work log entries. */
    private WorkRepository $workRepository;

    /** Repository for the guest-access credential. */
    private GuestAccessRepository $guestAccessRepository;

    /** Currently authenticated user, or null. */
    private ?User $currentUser = null;

    /** Human-readable page name used in sidebar / header. */
    private string $currentPage = "Nepojmenovaná stránka";

    /**
     * Tailwind gradient classes used for per-person avatar coloring.
     * Kept in sync with the AVATAR_COLORS array in app/includes/scripts.php.
     */
    private const AVATAR_COLORS = [
        "from-red-500 to-orange-700", "from-red-500 to-amber-700",
        "from-red-500 to-yellow-700", "from-red-500 to-lime-700",
        "from-orange-600 to-amber-700", "from-orange-600 to-yellow-700",
        "from-orange-600 to-lime-700", "from-orange-600 to-green-600",
        "from-amber-600 to-yellow-700", "from-amber-600 to-lime-700",
        "from-amber-600 to-green-600", "from-amber-600 to-emerald-600",
        "from-yellow-600 to-lime-700", "from-yellow-600 to-green-600",
        "from-yellow-600 to-emerald-600", "from-yellow-600 to-teal-600",
        "from-lime-600 to-green-600", "from-lime-600 to-emerald-600",
        "from-lime-600 to-teal-600", "from-lime-600 to-cyan-600",
        "from-green-500 to-emerald-600", "from-green-500 to-teal-600",
        "from-green-500 to-cyan-600", "from-green-500 to-sky-600",
        "from-emerald-500 to-teal-600", "from-emerald-500 to-cyan-600",
        "from-emerald-500 to-sky-600", "from-emerald-500 to-blue-600",
        "from-teal-500 to-cyan-600", "from-teal-500 to-sky-600",
        "from-teal-500 to-blue-600", "from-teal-500 to-indigo-600",
        "from-cyan-500 to-sky-600", "from-cyan-500 to-blue-600",
        "from-cyan-500 to-indigo-600", "from-cyan-500 to-violet-600",
        "from-sky-500 to-blue-600", "from-sky-500 to-indigo-600",
        "from-sky-500 to-violet-600", "from-sky-500 to-purple-600",
        "from-blue-500 to-indigo-600", "from-blue-500 to-violet-600",
        "from-blue-500 to-purple-600", "from-blue-500 to-fuchsia-600",
        "from-indigo-500 to-violet-600", "from-indigo-500 to-purple-600",
        "from-indigo-500 to-fuchsia-600", "from-indigo-500 to-pink-600",
        "from-violet-500 to-purple-600", "from-violet-500 to-fuchsia-600",
        "from-violet-500 to-pink-600", "from-violet-500 to-rose-600",
        "from-purple-500 to-fuchsia-600", "from-purple-500 to-pink-600",
        "from-purple-500 to-rose-600", "from-purple-500 to-red-600",
        "from-fuchsia-500 to-pink-600", "from-fuchsia-500 to-rose-600",
        "from-fuchsia-500 to-red-600", "from-fuchsia-500 to-orange-700",
        "from-pink-500 to-rose-600", "from-pink-500 to-red-600",
        "from-pink-500 to-orange-700", "from-pink-500 to-amber-700",
    ];

    /**
     * Boot the application: load config, connect to DB, run migrator,
     * instantiate all repositories, configure session settings, and
     * attempt to restore the current user from the session cookie.
     */
    public function __construct()
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");

        $this->configuration = new Configuration();

        try {
            if ($this->configuration->development) {
                ini_set("display_errors", "1");
                ini_set("display_startup_errors", "1");
                error_reporting(E_ALL);
            }

            $this->database = new Database($this->configuration);

            (new Migrator($this->database))->migrate();

            $this->assignmentRepository = new AssignmentRepository($this->database);
            $this->autoAssignRepository = new AutoAssignRepository($this->database);
            $this->categoryRepository = new CategoryRepository($this->database);
            $this->priorityRepository = new PriorityRepository($this->database);
            $this->roomRepository = new RoomRepository($this->database);
            $this->sessionRepository = new SessionRepository($this->database);
            $this->teacherRepository = new TeacherRepository($this->database);
            $this->ticketRepository = new TicketRepository($this->database);
            $this->ticketEventRepository = new TicketEventRepository($this->database);
            $this->userRepository = new UserRepository($this->database);
            $this->workRepository = new WorkRepository($this->database);
            $this->guestAccessRepository = new GuestAccessRepository($this->database);

            session_name($this->configuration->sessionCookie);
            session_set_cookie_params([
                "lifetime" => $this->configuration->sessionLifetime,
                "samesite" => "Strict",
            ]);

            $this->refreshSession();
        } catch (Exception $exception) {
            if ($this->configuration->development) print_r($exception);
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

    public function getTicketEventRepository(): TicketEventRepository
    {
        return $this->ticketEventRepository;
    }

    public function getUserRepository(): UserRepository
    {
        return $this->userRepository;
    }

    public function getWorkRepository(): WorkRepository
    {
        return $this->workRepository;
    }

    public function getGuestAccessRepository(): GuestAccessRepository
    {
        return $this->guestAccessRepository;
    }

    /**
     * Start a PHP session if one is not already active.
     *
     * Does not regenerate the session ID — that only happens once, at login
     * (see {@see createUserSession}). Regenerating it on every request (as
     * this used to) races the session cookie against itself whenever a page
     * fires more than one request in quick succession (e.g. two fetches
     * after a mutation), intermittently logging the user out.
     */
    private function startSession(): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Restore the current user from the DB session stored in $_SESSION.
     */
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

    /**
     * Create a new session for a user and store its ID in $_SESSION.
     *
     * If singleSession mode is enabled, all previous sessions for the user are deleted.
     *
     * @param int $user_id
     */
    public function createUserSession($user_id): void
    {
        if ($this->configuration->singleSession) $this->getSessionRepository()->deleteUserSessions($user_id);

        $session_id = $this->getSessionRepository()->addSession($user_id);
        $this->startSession();
        session_regenerate_id(true);
        $_SESSION["session"] = $session_id;
    }

    /** Destroy the current PHP session. */
    public function destroySession(): void
    {
        $this->startSession();
        session_destroy();
    }

    /**
     * Mark the current PHP session as an authenticated guest (unauthenticated
     * ticket submission on index.php), after a successful guest login.
     */
    public function authenticateGuestSession(): void
    {
        $this->startSession();
        session_regenerate_id(true);
        $_SESSION["guest_authenticated"] = true;
    }

    /** Whether the current PHP session is an authenticated guest. */
    public function isGuestSessionAuthenticated(): bool
    {
        $this->startSession();
        return !empty($_SESSION["guest_authenticated"]);
    }

    /** Clear the current session's guest authentication flag. */
    public function clearGuestSession(): void
    {
        $this->startSession();
        unset($_SESSION["guest_authenticated"]);
    }

    /** Redirect to the login page if no user is authenticated. */
    public function checkUser(): void
    {
        if (getApplication()->getUser() == null) {
            getApplication()->redirectInternally("login");
        }
    }

    /**
     * Redirect to a page by its script name (without .php extension).
     *
     * @param string $page
     */
    public function redirectInternally($page): void
    {
        header("Location: $page.php");
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

    /**
     * Extract the first two uppercase letters from a string.
     *
     * Falls back to the first two characters if no uppercase letters are found.
     *
     * @param string $string
     * @return string At most 2 characters.
     */
    public function getInitials(string $string): string
    {
        $capitals = preg_replace('/[^\p{Lu}]/u', '', $string);
        $source = $capitals ?: $string;
        $result = mb_substr($source, 0, 2);
        return $result ?: '?';
    }

    /**
     * Get the Tailwind gradient classes for a person's avatar, keyed by their
     * user/teacher ID so the same person always gets the same color everywhere.
     *
     * @param int $id
     * @return string e.g. "from-blue-500 to-indigo-600"
     */
    public function getAvatarGradient(int $id): string
    {
        $index = (($id % count(self::AVATAR_COLORS)) + count(self::AVATAR_COLORS)) % count(self::AVATAR_COLORS);
        return self::AVATAR_COLORS[$index];
    }
}

$application = new SPSTickets();

function getApplication(): TicketsApplication
{
    global $application;
    return $application;
}