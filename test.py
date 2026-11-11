rolls = [1, 2, 4, 3, 4]
if rolls.count(4) == 2:
    print("ballright")
    rolls = [d for d in rolls if d != 4]
print(rolls)
