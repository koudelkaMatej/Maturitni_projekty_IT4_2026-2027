import mysql.connector
from settings import *
from gameS import Score

mydb = mysql.connector.connect(
    host = HOST
    ,user = USER 
    ,password = PASSWORD
    ,database = DATABASE
)
