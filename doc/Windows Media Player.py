# @IPA
import time
# Daten welche durch GET nach ?WMP gesendet wurden.
eventdata = eg.event.payload

# In diesem Fall wird nur ein Parameter erwartet, ohne zusätzlichen GET-Parameter
try:
    action = eventdata[0]
except:
    print("No parameter was given.")
    eg.Exit()

# Funktionen werden definiert
def start():
    """
    Startet WMP wenn dieses noch nicht gestarted wurde.
    Wenn dieser bereits gestartet wurde, wird er in den Vordergrund gesetzt.
    """
    wmpWindow = eg.WindowMatcher('wmplayer.exe', 'Windows Media Player')()
    import win32gui
    if not wmpWindow :
        eg.plugins.System.Execute(u'C:\\Program Files (x86)\\Windows Media Player\\wmplayer.exe')
    else:
        window = wmpWindow[0]
        win32gui.SetForegroundWindow(window)
        win32gui.SetActiveWindow(window)


def next():
    """
    Nächstes Medium
    """
    eg.plugins.Window.SendKeys(u'{MediaNextTrack}', False, 2)

def previous():
    """
    Vorheriges Medium
    """
    eg.plugins.Window.SendKeys(u'{MediaPrevTrack}', False, 2)

def play():
    """
    Startet Medium
    """
    eg.plugins.Window.SendKeys(u'{MediaPlayPause}', False, 2)

def forward():
    """
    Vorspulen
    """
    eg.plugins.Window.SendKeys(u'{Ctrl+Shift+f}', False, 2)
    time.sleep(5)
    eg.plugins.Window.SendKeys(u'{Ctrl+Shift+f}', False, 2)

def rewind():
    """
    Zurückspulen
    """
    eg.plugins.Window.SendKeys(u'{Ctrl+Shift+f}', False, 2)
    time.sleep(5)
    eg.plugins.Window.SendKeys(u'{Ctrl+Shift+f}', False, 2)

def mute():
    """
    Stumm ein/aus
    """
    eg.plugins.Window.SendKeys(u'{F7}', False, 2)

def fullscreen():
    """
    Vollbild ein/aus
    """
    eg.plugins.Window.SendKeys(u'{Alt+Enter}', False, 2)

# Aktionssting zu Aktionen mapping.
actions = {
    'start': start,
    'next': next,
    'previous': previous,
    'play': play,
    'forward': forward,
    'rewind': rewind,
    'mute': mute,
    'fullscreen': fullscreen
}

# Führt Funktion in actions dictionary aus.
try:
    fun = actions[action]
except:
    print("Action %s was not found." % action)
    eg.Exit()
fun()