# @IPA
# Daten welche durch GET nach ?VLC gesendet wurden.
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
    Startet VLC wenn dieses noch nicht gestarted wurde.
    Falls dieses bereits gestartet wurde, wird es in den Vordergrund gesetzt.
    """
    vlcWindow = eg.WindowMatcher('vlc.exe', '{*} - VLC media player')()
    import win32gui
    if not vlcWindow :
        eg.plugins.System.Execute(u'C:\\Program Files\\VideoLAN\\VLC\\vlc.exe')
    else:
        window = vlcWindow[0]
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
    eg.plugins.Window.SendKeys(u'{Ctrl+Right}', False, 2)

def rewind():
    """
    Zurückspulen
    """
    eg.plugins.Window.SendKeys(u'{Ctrl+Left}', False, 2)

def mute():
    """
    Stumm ein/aus
    """
    eg.plugins.Window.SendKeys(u'{m}', False, 2)

def fullscreen():
    """
    Vollbild ein/aus
    """
    eg.plugins.Window.SendKeys(u'{f}', False, 2)

def time():
    """
    Zeigt Zeit des Mediums an.
    """
    import time
    for i in range(10):
        eg.plugins.Window.SendKeys(u'{t}', False, 2)
        time.sleep(.5)

# Aktionssting zu Aktionen mapping.
actions = {
    'start': start,
    'next': next,
    'previous': previous,
    'play': play,
    'forward': forward,
    'rewind': rewind,
    'mute': mute,
    'fullscreen': fullscreen,
    'time': time
}

# Führt Funktion in actions dictionary aus.
try:
    fun = actions[action]
except:
    print("Action %s was not found." % action)
    eg.Exit()
fun()
