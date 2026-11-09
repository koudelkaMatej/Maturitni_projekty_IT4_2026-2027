import pygame

USER = "?"
HOST = "?"
PASSWORD = "?"
DATABASE = "http://dbs.spskladno.cz/myadmin/"





######################################################################################

WHITE = (245, 245, 245)
BLACK = (20, 20, 20)
BLUE = (70, 130, 255)
DARK_BLUE = (45, 95, 210)
SHADOW = (15, 15, 20)


WIDTH = 1200
HEIGHT = 700
BG = (32, 34, 46)


# Barvy, které jdou měnit v nastavení (dict je sdílený, takže změny platí všude)
COLOR_PALETTE = [
    ("White", WHITE),
    ("Black", BLACK),
    ("Red", (210, 50, 50)),
    ("Orange", (240, 140, 40)),
    ("Yellow", (240, 210, 60)),
    ("Green", (60, 170, 90)),
    ("Blue", DARK_BLUE),
    ("Purple", (130, 70, 200)),
    ("Pink", (235, 120, 180)),
    ("Gray", (120, 120, 130)),
]

theme = {
    "dice": WHITE,
    "pips": BLACK,
    "menu_bg": WHITE,
    "button": DARK_BLUE,
}
ROLL_DURATION = 2.5
DICE_SIZE = 140


MAX_DICE = 6
DICE_GAP = 20
DICE_ROW_WIDTH = 560  #DŮLEŽÍTÉ 

running = True
rolling = False
roll_start = 0
last_flicker = 0
Score = 0
dice_count = 1
current_values = [1]
final_values = [1]





##########screen#############

screen = pygame.display.set_mode((600, 600))
pygame.display.set_caption("KostkyMaturita")


##########title image#############

TITLE_RECT = pygame.Rect(0, 0, 500, 230)
TITLE_RECT.center = (300, 140)

title_image = pygame.image.load("/home/sysel/Projects/TEST/Maturitni_projekty_IT4_2026-2027/dice.png").convert_alpha()
title_image = pygame.transform.smoothscale(title_image, TITLE_RECT.size)
