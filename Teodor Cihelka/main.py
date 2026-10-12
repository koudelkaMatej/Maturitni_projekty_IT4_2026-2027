import pygame
from settings import *
pygame.init()
pygame.font.init()


font = pygame.font.Font(None, 30)
screen = pygame.display.set_mode((SCREEN_WIDTH, SCREEN_HEIGHT))
clock = pygame.time.Clock()
clock.tick(FPS)
running = True
konec = pygame.USEREVENT + 1

def game_menu():
    game_menu_running = True
    while game_menu_running:
        screen.fill(GREEN)
        pygame.display.flip()





def settings_menu():
    settings_menu_running = True
    while settings_menu_running:
        screen.fill(WHITE)
        for event in pygame.event.get():
            if event.type == pygame.QUIT:
                settings_menu_running = False
            if event.type == pygame.MOUSEBUTTONDOWN:
                if settingsofftextrect.collidepoint(event.pos):
                    settings_menu_running = False


        settingsofftext =  font.render("X", True, BLACK)
        settingsofftextrect = settingsofftext.get_rect(center=(SCREEN_WIDTH-15,20))
        settingsofftextrect = settingsofftextrect.inflate(20,20)
        pygame.draw.rect(screen, BRIGHT_BLUE, settingsofftextrect)
        screen.blit(settingsofftext, settingsofftextrect)
        pygame.display.flip()

def main_menu():
    main_menu_running = True
    while main_menu_running:
        screen.fill(WHITE)
        startGameText = font.render("Start Game", True, BLACK)
        startGameTextRect = startGameText.get_rect(center=(SCREEN_WIDTH // 2, SCREEN_HEIGHT // 2-100))
        startGameTextRect = startGameTextRect.inflate(120, 40)
        pygame.draw.rect(screen, BRIGHT_ORANGE, startGameTextRect)
        screen.blit(startGameText, startGameTextRect)
        settingsText = font.render("Settings", True, BLACK)
        settingsTextRect = settingsText.get_rect(center=(SCREEN_WIDTH // 2, SCREEN_HEIGHT // 2 ))
        settingsTextRect = settingsTextRect.inflate(120, 40)
        pygame.draw.rect(screen, BRIGHT_BLUE, settingsTextRect)
        screen.blit(settingsText, settingsTextRect)
        exitText = font.render("Exit", True, BLACK)
        exitTextRect = exitText.get_rect(center=(SCREEN_WIDTH // 2, SCREEN_HEIGHT // 2 + 100))
        exitTextRect = exitTextRect.inflate(120, 40)
        pygame.draw.rect(screen, BRIGHT_GREEN, exitTextRect)
        screen.blit(exitText, exitTextRect)
        for event in pygame.event.get():
            if event.type == pygame.QUIT:
                main_menu_running = False
            if event.type == pygame.MOUSEBUTTONDOWN:
                if exitTextRect.collidepoint(event.pos):
                    main_menu_running = False
                if settingsTextRect.collidepoint(event.pos):
                    settings_menu()
                if startGameTextRect.collidepoint(event.pos):
                    game_menu()

        pygame.display.flip()



while running:
    main_menu()
    running = False