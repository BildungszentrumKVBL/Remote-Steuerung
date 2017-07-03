# @IPA
# Daten welche durch GET nach ?PowerPoint gesendet wurden.
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
    Startet PowerPoint wenn dieses noch nicht gestarted wurde.
    Wenn PowerPoint bereits gestartet ist, wird die Präsentation gestartet order beendet.
    """
    ppWindow = eg.WindowMatcher('POWERPNT.exe', '{*} - PowerPoint')()
    import win32gui
    if not ppWindow :
        eg.plugins.System.Execute(u'C:\\Program Files (x86)\\Microsoft Office\\Office16\\POWERPNT.EXE')
    else:
        window = ppWindow[0]
        win32gui.SetForegroundWindow(window)
        win32gui.SetActiveWindow(window)
        eg.plugins.Window.SendKeys(u'{F5}', False, 2)

def next():
    """
    Nächste Folie
    """
    eg.plugins.Window.SendKeys(u'{RIGHT}', False, 2)

def previous():
    """
    Vorherige Folie
    """
    eg.plugins.Window.SendKeys(u'{LEFT}', False, 2)

def first():
    """
    Erste Folie
    """
    eg.plugins.Window.SendKeys(u'1', False, 2)
    eg.plugins.Window.SendKeys(u'{ENTER}', False, 2)

def last():
    """
    Letzte Folie
    """
    eg.plugins.Window.SendKeys(u'9999', False, 2)
    eg.plugins.Window.SendKeys(u'{ENTER}', False, 2)

def slide_x():
    """
    Springt zu definierter Folie
    """
    try:
        param = eventdata[1]
        slide_nr = param.split('=')
        if slide_nr[0] == 'slide':
            eg.plugins.Window.SendKeys(str(slide_nr[1]), False, 2)
            eg.plugins.Window.SendKeys(u'{ENTER}', False, 2)
    except:
        print("No slide was selected!")

def blank():
    """
    Folie verdecken
    """
    eg.plugins.Window.SendKeys(u'b', False, 2)

def play():
    """
    Startet Video
    """
    from win32api import GetSystemMetrics
    from time import sleep
    eg.plugins.Mouse.MoveAbsolute(GetSystemMetrics(0)/2, GetSystemMetrics(1)/2)
    eg.plugins.Mouse.GoDirection(accelerationFactor=10)
    sleep(.5)
    eg.plugins.Mouse.LeftButton()


# Aktionssting zu Aktionen mapping.
actions = {
    'start': start,
    'next': next,
    'first': first,
    'last': last,
    'previous': previous,
    'slide_x': slide_x,
    'blank': blank,
    'play': play
}

# Führt Funktion in actions dictionary aus.
try:
    fun = actions[action]
except:
    print("Action %s was not found." % action)
    eg.Exit()
fun()
