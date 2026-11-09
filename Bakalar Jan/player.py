import pygame

class Player(pygame.sprite.Sprite):
    def __init__(self, x, y, sprite_sheet_path="assets/player.png"):
        super().__init__()
        
        sheet = pygame.image.load(sprite_sheet_path).convert_alpha()
        tile_size = 64
        
        # --- 1. IDLE ANIMACE (Mřížka 64x64, pohled doprava = 3. řádek) ---
        idle_row_y = 3 * tile_size 
        self.idle_frames = []
        for col in range(2):
            frame = sheet.subsurface((col * tile_size, idle_row_y, tile_size, tile_size))
            scaled = pygame.transform.scale(frame, (256, 256))  # <-- ZMĚNA
            self.idle_frames.append(scaled)

        # --- 2. SLASH ANIMACE MEČEM (Spodní mřížka 192x192 px) ---
        big_tile = 192
        total_height = sheet.get_height()
        
        # Poslední řádek dole v bloku 192x192 odpovídá útoku DOPRAVA
        slash_row_y = total_height - big_tile
        
        self.slash_frames = []
        # 2. SLASH FRAMES (původně 384, 384 -> zvětšíme na 768, 768)
        for col in range(6):
            frame = sheet.subsurface((col * big_tile, slash_row_y, big_tile, big_tile))
            scaled = pygame.transform.scale(frame, (768, 768))  # <-- ZMĚNA
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
        # Držitel kotevního středu, aby Hráč při změně snímku neuskočil
        old_center = self.rect.center

        if self.state == "idle":
            self.current_frame += 0.05
            if self.current_frame >= len(self.idle_frames):
                self.current_frame = 0.0
            self.image = self.idle_frames[int(self.current_frame)]

        elif self.state == "slash":
            self.current_frame += 0.2
            if self.current_frame >= len(self.slash_frames):
                self.state = "idle"
                self.current_frame = 0.0
                self.image = self.idle_frames[0]
            else:
                self.image = self.slash_frames[int(self.current_frame)]

        # Přikotvení nového snímku na střed
        self.rect = self.image.get_rect(center=old_center)