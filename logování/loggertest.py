import logging



logger = logging.getLogger("cviceni2")
logger.setLevel(logging.DEBUG)


handler = logging.FileHandler("cviceni2.log", mode="w")

formatter = logging.Formatter("%(levelname)s: %(message)s")
handler.setFormatter(formatter)

logger.addHandler(handler)


logger.info("Program byl spuštěn.")
logger.warning("Toto je varování.")
logger.error("Nastala chyba.")

with open("cviceni2.log", "r") as soubor:
    obsah = soubor.read()

print(obsah)