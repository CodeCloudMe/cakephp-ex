// TODO - doesn't seem to be used.
Jelly.Debug.Dir = function(Obj)
{
	return;
	if (Jelly.Debug.Debug_Mode == Jelly.Debug.Level.All)
		if (window.console)
			if (console.dir)
				console.dir(Obj);
};