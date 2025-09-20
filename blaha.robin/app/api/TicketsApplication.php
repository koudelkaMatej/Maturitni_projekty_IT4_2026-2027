<?php

interface TicketsApplication
{
    /**
     * Repository to store all ticket assignments to users
     * @return AssignmentRepository
     */
    public function getAssignmentRepository(): AssignmentRepository;

    /**
     * Repository to store automatic ticket assignments to users per category
     * @return AutoAssignRepository
     */
    public function getAutoAssignRepository(): AutoAssignRepository;

    /**
     * Repository to store ticket categories
     * @return CategoryRepository
     */
    public function getCategoryRepository(): CategoryRepository;

    /**
     * Repository to store ticket priorities
     * @return PriorityRepository
     */
    public function getPriorityRepository(): PriorityRepository;

    /**
     * Repository to store ticket rooms
     * @return RoomRepository
     */
    public function getRoomRepository(): RoomRepository;

    /**
     * Repository to store user login sessions
     * @return SessionRepository
     */
    public function getSessionRepository(): SessionRepository;

    /**
     * Repository to store all teachers
     * @return TeacherRepository
     */
    public function getTeacherRepository(): TeacherRepository;

    /**
     * Repository to store all tickets
     * @return TicketRepository
     */
    public function getTicketRepository(): TicketRepository;

    /**
     * Repository to store all users (users extend teachers)
     * @return UserRepository
     */
    public function getUserRepository(): UserRepository;

    /**
     * Repository to store all works for each ticket
     * @return WorkRepository
     */
    public function getWorkRepository(): WorkRepository;
}