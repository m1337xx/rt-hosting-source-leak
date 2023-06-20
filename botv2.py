import discord
from discord.ext import commands
from discord import ApplicationCommandInteraction as ACI, SlashCommandOption as Option, Permissions, \
    SlashCommandOptionChoice as Choice
import pymysql
import time
import random

# INITIALISATION

last_coin_timestamps = {}
coin_timeout = 10
client = commands.Bot(command_prefix="$", intents=discord.Intents.all(), sync_commands=True)
client.remove_command("help")


# CHECK IF MESSAGE NOT PINNED
def is_not_pinned(mess):
    return not mess.pinned


@client.slash_command(name="coins", description="Schaue nach wer wie viele Coins hat!", allow_dm=False)
async def coinscmd(ctx: ACI,
                   member: Option(discord.Member, name="member",
                                  description="Der Benutzer dessen Coins du sehen möchtest", required=False) = None):
    conn = pymysql.connect(
        host='localhost',
        user='rth',
        password='ThLDxjoPs%Kq03fVNr4lESc4KS60^KKh',
        db='rth'
    )

    cursor = conn.cursor()

    if member is not None:
        cursor.execute(f"SELECT `coins` FROM `coinsys` WHERE `dcid`='{member.id}'")
        result = cursor.fetchone()

        coins = result[0]

        if result is not None:
            ed = f"Der Benutzer ({member.mention}) ist im Besitz von **{coins}** Coins."
        else:
            ed = f"Der Benutzer ({member.mention}) besitzt aktuell noch **keine** Coins."
        embed = discord.Embed(title="Coin System",
                              description=ed)
    else:
        cursor.execute(f"SELECT `coins` FROM `coinsys` WHERE `dcid`='{ctx.author.id}'")
        result = cursor.fetchone()

        coins = result[0]

        if result is not None:
            ed = f"Du bist im Besitz von **{coins}** Coins."
        else:
            ed = f"Du besitzt aktuell noch **keine** Coins."
        embed = discord.Embed(title="Coin System",
                              description=ed)

    await ctx.respond(embed=embed, delete_after=10)


@client.slash_command(name="clear", description="Lösche eine gegebene Anzahl an Nachrichten",
                      default_required_permissions=Permissions(manage_messages=True), allow_dm=False)
async def clearcmd(ctx: ACI,
                   amount: Option(int, name="amount", description="Wie viele Nachrichten gelöscht werden.",
                                  required=True),
                   pinned: Option(bool, name="pinned", description="Ob du gepinnte Nachrichten gelöscht werden sollen.",
                                  required=False, default=False)):
    count = amount
    if not pinned:
        deleted = await ctx.channel.purge(limit=count, check=is_not_pinned)
    else:
        deleted = await ctx.channel.purge(limit=count, check=None)
    await ctx.respond(f"> {len(deleted)} Messages deleted!", delete_after=5)


@client.listen()
async def on_ready():
    print(f"{client.user.global_name} v2 started!")


@client.listen()
async def on_message(message):
    # AVOID SPAM & (USELESS) ERRORS

    if message.author.bot:
        return
    if not message.guild:
        return

    # COIN SYS; IF YOU'RE A SKID, LEAVE IT!

    conn = pymysql.connect(
        host='localhost',
        user='rth',
        password='ThLDxjoPs%Kq03fVNr4lESc4KS60^KKh',
        db='rth'
    )

    cursor = conn.cursor()
    cursor.execute(f"SELECT * FROM `coinsys` WHERE `dcid`='{message.author.id}'")
    result = cursor.fetchone()

    if result is None:
        cursor.execute(f"INSERT INTO `coinsys` (`dcid`, `coins`) VALUES ({message.author.id}, 5)")
        conn.commit()
        await message.reply("Du wurdest in die Datenbank eingetragen!\n\n"
                            "Deine Coins siehst du hier: https://rth.cft-devs.xyz/panel oder per Befehl: `-coins`",
                            delete_after=10)
    else:
        last_timestamp = last_coin_timestamps.get(message.author.id, 0)
        current_timestamp = time.time()

        if current_timestamp - last_timestamp >= coin_timeout:
            plusCoins = random.randrange(1, 5)
            cursor.execute(f"UPDATE `coinsys` SET `coins` = `coins` + {plusCoins} WHERE `dcid`={message.author.id}")
            conn.commit()
            last_coin_timestamps[message.author.id] = current_timestamp

    conn.close()


client.run("MTExMzUyMjUwNjM1MjA1MDE5Ng.GNBspZ.WRLQ2Sok7msycD4hTz6eAlboGzOS6n8ufchqNQ")
