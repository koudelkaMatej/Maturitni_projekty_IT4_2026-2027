import pygame

class Boss(pygame.sprite.Sprite):
    def __init__(self, x, y, sprite_sheet_path="assets/boss.png"):
        super().__init__()
        
        sheet = pygame.image.load(sprite_sheet_path).convert_alpha()
        tile_size = 64
        
        # --- 1. IDLE ANIMACE (64x64 px) ---
        idle_row_y = 1 * tile_size 
        self.idle_frames = []
        for col in range(2):
            frame = sheet.subsurface((col * tile_size, idle_row_y, tile_size, tile_size))
            scaled = pygame.transform.scale(frame, (256, 256))  # <-- ZMĚNA
            self.idle_frames.append(scaled)

       # --- 2. SLASH ANIMACE S KOSOU (192x192 px) ---
        big_tile = 192
        total_height = sheet.get_height()

        # Začátek bloku 192x192 px (4 řádky nad koncem souboru)
        first_big_row_y = total_height - (4 * big_tile)

        # ZMĚNA SMĚRU ZDE (vynásobení číslem 0 až 3):
        # 0 = Nahoru, 1 = Vlevo, 2 = Dolů, 3 = Vpravo
        direction_index = 1  # <-- TADY ZMĚŇ ČÍSLO PRO ZMĚNU SMĚRU

        slash_row_y = first_big_row_y + (direction_index * big_tile)

        self.slash_frames = []
        for col in range(6):
            frame = sheet.subsurface((col * big_tile, slash_row_y, big_tile, big_tile))
            scaled = pygame.transform.scale(frame, (768, 768))
            self.slash_frames.append(scaled)
            
        self.state = "idle"
        self.current_frame = 0.0
        
        self.image = self.idle_frames[0]
        self.rect = self.image.get_rect(topleft=(x, y))

    def attack(self):
        if self.state != "slash":
            self.state = "slash"
            self.current_frame = 0.0

    def update(self):
        # Uložíme si střed postavy před výměnou snímku
        old_center = self.rect.center

        if self.state == "idle":
            self.current_frame += 0.05
            if self.current_frame >= len(self.idle_frames):
                self.current_frame = 0.0
            self.image = self.idle_frames[int(self.current_frame)]

        elif self.state == "slash":
            self.current_frame += 0.18
            if self.current_frame >= len(self.slash_frames):
                self.state = "idle"
                self.current_frame = 0.0
                self.image = self.idle_frames[0]
            else:
                self.image = self.slash_frames[int(self.current_frame)]

        # Vyrovnáme obdélník na střed, aby Boss při změně velikosti snímku neuskočil
        self.rect = self.image.get_rect(center=old_center)