#include "../MQ2Plugin.h"

/*
* MQ2NoMountModels
* This plugin simply allows you to use a horse regardless of
* whether you have luclin modles on for that toon.
* You still need to have horse models enabled for this to work.
* Note the offset for this will need to be updated every patch.


	todo: generella mem-patch funktioner
	- med generella funktioner, l�gg till st�d f�r n�gra av docracksen. t.ex sl� p� spell awareness
*/

PreSetup("MQ2NoMountModels");
PLUGIN_VERSION(1.01);

#define address 0x4F3C70 //Mon Feb 19, 2007 6:18 pm

class a
{
public:
   bool b();
   bool c()
   {
      return false;
   }
};

DETOUR_TRAMPOLINE_EMPTY(bool a::b(void));

PLUGIN_API VOID InitializePlugin(VOID)
{
   EzDetour(address,&a::c,&a::b);
}

PLUGIN_API VOID ShutdownPlugin(VOID)
{
   RemoveDetour(address);
} 