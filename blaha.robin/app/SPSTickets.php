<?php

$application = new SPSTickets();

function getApplication(): TicketsApplication
{
    global $application;
    return $application;
}

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

    public function __construct()
    {
        $this->configuration = new Configuration();
        $this->database = new Database($this->configuration);

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
}