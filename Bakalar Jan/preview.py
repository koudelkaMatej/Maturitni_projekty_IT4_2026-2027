import pygame

pygame.init()

# Načtení obrázku Bosse
boss_sheet = pygame.image.load("assets/boss.png").convert_alpha()
TILE_SIZE = 64

font = pygame.font.SysFont(None, 18)

# Procházení mřížky a kreslení červených čar s čísly
for row in range(boss_sheet.get_height() // TILE_SIZE):
    for col in range(boss_sheet.get_width() // TILE_SIZE):
        x = col * TILE_SIZE
        y = row * TILE_SIZE
        # Nakreslí rámeček kolem políčka
        pygame.draw.rect(boss_sheet, (255, 0, 0), (x, y, TILE_SIZE, TILE_SIZE), 1)
        # Vepíše číslo [řádek, sloupeček]
        txt = font.render(f"{row},{col}", True, (255, 255, 0))
        boss_sheet.blit(txt, (x + 2, y + 2))

# Uložení nového obrázku s mřížkou
pygame.image.save(boss_sheet, "assets/boss_preview.png")
print("Hotovo! Ve složce assets najdeš nový obrázek 'boss_preview.png'.")

pygame.quit()