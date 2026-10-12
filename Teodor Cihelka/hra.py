import pygame
from settings import *

class Flappy(pygame.sprite.Sprite):
    def __init__(self):
        super().__init__()
        self.animation_interval = 500
        self.animation_timer = 0 # počet snímků od poslední výměny obrázku
        self.animation_index = 0 #indeex aktuálního obrázku
        self.animations = []
        self.animations.append(pygame.image.load('assets/bird1.png').convert_alpha())
        self.animations.append(pygame.image.load('assets/bird2.png').convert_alpha())
        self.animations.append(pygame.image.load('assets/bird3.png').convert_alpha())
        self.animations.append(pygame.image.load('assets/bird2.png').convert_alpha())
        for i in range(len(self.animations)):
            self.animations[i] =pygame.transform.scale(self.animations[i], (50, 50))

        self.image = self.animations[0]
        self.rect = self.image.get_rect(center=(50, SCREEN_HEIGHT // 2))

        self.gravity = 0
        self.jump_strength = -10
        self.velocity = 0

    def update(self):
        self.image = self.animations[self.animation_index] #aktualizace obrázku na základě indexu
        self.animation_timer +=1 #zvýšení časovače
        if self.animation_timer >= self.animation_interval: #pokud uplynul čas pro výměnu obrázku
            self.animation_index += 1 # zvýšení indexu obrázku
            if self.animation_index >= len(self.animations): #pokud je index větší než počet obrázků v listu
                self.animation_index = 0 # restuji index na první obrázek
            self.animation_timer = 0 # restuji časovač
        self.velocity += self.gravity
        self.rect.y += self.velocity