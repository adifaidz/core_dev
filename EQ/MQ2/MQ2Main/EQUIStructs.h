/*****************************************************************************
    MQ2Main.dll: MacroQuest2's extension DLL for EverQuest
    Copyright (C) 2002-2003 Plazmic, 2003-2005 Lax

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as published by
    the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
******************************************************************************/

namespace EQUIStructs
{


enum UIType
{
	UI_Unknown=-1,
	UI_Class=0,
	UI_RGB=1,
	UI_Point=2,
	UI_Size=3,
	UI_TextureInfo=4,
	UI_Frame=5,
	UI_Ui2DAnimation=6,
	UI_ButtonDrawTemplate=7,
	UI_GaugeDrawTemplate=8,
	UI_SpellGemDrawTemplate=9,
	UI_FrameTemplate=10,
	UI_ScrollbarDrawTemplate=11,
	UI_WindowDrawTemplate=12,
	UI_SliderDrawTemplate=13,
	UI_ScreenPiece=14,
	UI_StaticScreenPiece=15,
	UI_StaticAnimation=16,
	UI_StaticTintedBlendAnimation=17,
	UI_StaticText=18,
	UI_StaticFrame=19,
	UI_StaticHeader=20,
	UI_Control=21,
	UI_ListboxColumn=22,
	UI_Listbox=23,
	UI_Button=24,
	UI_Gauge=25,
	UI_SpellGem=26,
	UI_HtmlComponent=27,
	UI_InvSlot=28,
	UI_EditBox=29,
	UI_Slider=30,
	UI_Label=31,
	UI_STMLBox=32,
	UI_Combobox=33,
	UI_Page=34,
	UI_TabBox=35,
	UI_LayoutBox=36,
	UI_HorizontalLayoutBox=37,
	UI_VerticalLayoutBox=38,
	UI_Screen=40,
	UI_SuiteDefaults=41,
};

// ***************************************************************************
// Structures
// ***************************************************************************

// 10-24-2003 Lax - Massive overhaul.
// See http://macroquest.sourceforge.net/phpBB2/viewtopic.php?t=3569 for
// standards on structures to make things easier on everyone!
// -- ONLY applies to EQ structs, not MQ structs like alerts, etc.


//5-15-2003   eqmule
typedef struct _CXSTR {
/*0x00*/   DWORD   Font;            // maybe, dont know.  04 = Window 01 = button
/*0x04*/   DWORD   MaxLength;
/*0x08*/   DWORD   Length;
/*0x0c*/   BOOL    Encoding; // 0: ASCII, 1:Unicode
/*0x10*/   PCRITICAL_SECTION pLock;
/*0x14*/   CHAR    Text[1]; // Stub, can be anywhere from Length to MaxLength (which is how much is malloc'd to this CXStr)
} CXSTR, *PCXSTR;


typedef struct _EQCURRENTSELECTION {
/*0x00*/   DWORD   Unknown;
/*0x04*/   DWORD   SelectedWnd;//address to selection in tree
/*0x08*/   DWORD   Unknownlodncrap;
/*0x0c*/   DWORD   NumberOfSlots;//merchantslots == 79
/*0x10*/   DWORD   TextureAnim;
/*0x14*/
} EQCURRENTSELECTION, *PEQCURRENTSELECTION;

// TO BE REMOVED.
/*
typedef struct _EQSLOTLIST {
            DWORD   Parent;
            DWORD   InvSlots[372];//0-21 inv 22-30 bags
} EQSLOTLIST, *PEQSLOTLIST;
/**/

// Lax
// Actual Size 0x140 9-5-2007
typedef struct _CSIDLWNDVFTABLE {
/*0000*/ LPVOID  IsValid;
/*0004*/ LPVOID  vector_deleting_destructor;
/*0008*/ LPVOID  DrawNC;
/*000C*/ LPVOID  PostDraw;
/*0010*/ LPVOID  PostDraw2;
/*0014*/ LPVOID  DrawCursor;
/*0018*/ LPVOID  OnResize;
/*001C*/ LPVOID  PostDraw3;
/*0020*/ LPVOID  DrawBackground;
/*0024*/ LPVOID  DrawTooltip;
/*0028*/ LPVOID  GetMinimizedRect;
/*002C*/ LPVOID  DrawTitleBar;
/*0030*/ LPVOID  GetCursorToDisplay;
/*0034*/ LPVOID  HandleLButtonDown;
/*0038*/ LPVOID  HandleLButtonUp;
/*003C*/ LPVOID  HandleLButtonHeld;
/*0040*/ LPVOID  HandleLButtonUpAfterHeld;
/*0044*/ LPVOID  HandleRButtonDown;
/*0048*/ LPVOID  HandleRButtonUp;
/*004C*/ LPVOID  HandleRButtonHeld;
/*0050*/ LPVOID  HandleRButtonUpAfterHeld;
/*0054*/ LPVOID  HandleWheelButtonDown;
/*0058*/ LPVOID  HandleWheelButtonUp;
/*005C*/ LPVOID  HandleMouseMove;
/*0060*/ LPVOID  HandleWheelMove;
/*0064*/ LPVOID  GetDragDropCursor;
/*0068*/ LPVOID  PostDraw4;
/*006c*/ LPVOID  OnDragDrop;
/*0070*/ LPVOID  GetClickStickCursor;
/*0074*/ LPVOID  QueryDropOK;
/*0078*/ LPVOID  OnClickStick;
/*007C*/ LPVOID  GetClickStickCursor2;
/*0080*/ LPVOID  QueryClickStickDropOK;
/*0084*/ LPVOID  WndNotification;
/*0088*/ LPVOID  ShowWindow;     // Activate
/*008C*/ LPVOID  Deactivate;
/*0090*/ LPVOID  OnShow;
/*0094*/ LPVOID  OnMove;
/*0098*/ LPVOID  OnResize2;
/*009c*/ LPVOID  OnMinimizeBox;
/*00A0*/ LPVOID  Unknown0xa0;
/*00A4*/ LPVOID  OnTileBox;
/*00A8*/ LPVOID  PostDraw5;
/*00AC*/ LPVOID  OnMove2;
/*00B0*/ LPVOID  OnMove3;
/*00B4*/ LPVOID  OnProcessFrame;
/*00B8*/ LPVOID  OnVScroll;
/*00BC*/ LPVOID  OnHScroll;
/*00C0*/ LPVOID  PostDraw6;
/*00C4*/ LPVOID  OnMove4;
/*00C8*/ LPVOID  Show;
/*00CC*/ LPVOID  Unknown0x0cc;
/*00D0*/ LPVOID  Unknown0x0d0;
/*00D4*/ LPVOID  OnMove5;
/*00D8*/ LPVOID  RequestDockInfo;
/*00DC*/ LPVOID  GetTooltip;
/*00E0*/ LPVOID  HitTest;
/*00E4*/ LPVOID  GetHitTestRect;
/*00E8*/ LPVOID  GetInnerRect;
/*00EC*/ LPVOID  GetClientRect;
/*00F0*/ LPVOID  GetMinSize;
/*00F4*/ LPVOID  Unknown0x0f4;
/*00F8*/ LPVOID  GetUntileSize;
/*00FC*/ LPVOID  IsPointTransparent;
/*0100*/ LPVOID  SetDrawTemplate;
/*0104*/ LPVOID  Unknown0x104;
/*0108*/ LPVOID  Unknown0x108;
/*010C*/ LPVOID  Unknown0x10c;
/*0110*/ LPVOID  SetWindowTextA;
/*0114*/ LPVOID  Unknown0x114;
/*0118*/ LPVOID  SetVScrollPos;
/*011C*/ LPVOID  SetHScrollPos;
/*0120*/ LPVOID  SetAttributesFromSidl;
/*0124*/ LPVOID  nullsub_1;
/*0128*/ LPVOID  Unknown0x128;
/*012C*/ LPVOID  Unknown0x12c;
/*0130*/ LPVOID  PostDraw7;
/*0134*/ LPVOID  LoadIniInfo;
/*0138*/ LPVOID  StoreIniInfo;
/*013c*/ LPVOID  Unknown0x13c;
/*0140*/ 
} CSIDLWNDVFTABLE, *PCSIDLWNDVFTABLE;

// Lax
// Actual Size 0x134 9-5-2007
typedef struct _CXWNDVFTABLE {
/*0000*/ LPVOID  IsValid;
/*0004*/ LPVOID  vector_deleting_destructor;
/*0008*/ LPVOID  DrawNC;
/*000C*/ LPVOID  PostDraw;
/*0010*/ LPVOID  PostDraw2;
/*0014*/ LPVOID  DrawCursor;
/*0018*/ LPVOID  OnResize;
/*001C*/ LPVOID  PostDraw3;
/*0020*/ LPVOID  DrawBackground;
/*0024*/ LPVOID  DrawTooltip;
/*0028*/ LPVOID  GetMinimizedRect;
/*002C*/ LPVOID  DrawTitleBar;
/*0030*/ LPVOID  GetCursorToDisplay;
/*0034*/ LPVOID  HandleLButtonDown;
/*0038*/ LPVOID  HandleLButtonUp;
/*003C*/ LPVOID  HandleLButtonHeld;
/*0040*/ LPVOID  HandleLButtonUpAfterHeld;
/*0044*/ LPVOID  HandleRButtonDown;
/*0048*/ LPVOID  HandleRButtonUp;
/*004C*/ LPVOID  HandleRButtonHeld;
/*0050*/ LPVOID  HandleRButtonUpAfterHeld;
/*0054*/ LPVOID  HandleWheelButtonDown;
/*0058*/ LPVOID  HandleWheelButtonUp;
/*005C*/ LPVOID  HandleMouseMove;
/*0060*/ LPVOID  HandleWheelMove;
/*0064*/ LPVOID  GetDragDropCursor;
/*0068*/ LPVOID  PostDraw4;
/*006C*/ LPVOID  OnDragDrop;
/*0070*/ LPVOID  GetClickStickCursor;
/*0074*/ LPVOID  QueryDropOK;
/*0078*/ LPVOID  OnClickStick;
/*007C*/ LPVOID  GetClickStickCursor2;
/*0080*/ LPVOID  QueryClickStickDropOK;
/*0084*/ LPVOID  WndNotification;
/*0088*/ LPVOID  ShowWindow;     // Activate
/*008C*/ LPVOID  Deactivate;
/*0090*/ LPVOID  PostDraw5;
/*0094*/ LPVOID  OnMove;
/*0098*/ LPVOID  OnResize2;
/*009C*/ LPVOID  OnMinimizeBox;
/*00A0*/ LPVOID  Unknown0xa0;
/*00A4*/ LPVOID  OnTileBox;
/*00A8*/ LPVOID  PostDraw6;
/*00AC*/ LPVOID  OnMove2;
/*00B0*/ LPVOID  OnMove3;
/*00B4*/ LPVOID  OnProcessFrame;
/*00B8*/ LPVOID  OnVScroll;
/*00BC*/ LPVOID  OnHScroll;
/*00C0*/ LPVOID  PostDraw7;
/*00C4*/ LPVOID  OnMove4;
/*00C8*/ LPVOID  Show;
/*00CC*/ LPVOID  Unknown0x0cc;
/*00D0*/ LPVOID  Unknown0x0d0;
/*00D4*/ LPVOID  OnMove5;
/*00D8*/ LPVOID  RequestDockInfo;
/*00DC*/ LPVOID  GetTooltip;
/*00E0*/ LPVOID  HitTest;
/*00E4*/ LPVOID  GetHitTestRect;
/*00E8*/ LPVOID  GetInnerRect;
/*00EC*/ LPVOID  GetClientRect;
/*00F0*/ LPVOID  GetMinSize;
/*00F4*/ LPVOID  Unknown0x0f4;
/*00F8*/ LPVOID  GetUntileSize;
/*00FC*/ LPVOID  IsPointTransparent;
/*0100*/ LPVOID  SetDrawTemplate;
/*0104*/ LPVOID  Unknown0x104;
/*0108*/ LPVOID  Unknown0x108;
/*010c*/ LPVOID  Unknown0x10c;
/*0110*/ LPVOID  SetWindowTextA;
/*0114*/ LPVOID  Unknown0x110;
/*0118*/ LPVOID  SetVScrollPos;//->0
/*011c*/ LPVOID  SetHScrollPos;
/*0120*/ LPVOID  SetAttributesFromSidl;
/*0124*/ LPVOID  nullsub_1;
/*0128*/ LPVOID  Unknown0x128;
/*012c*/ LPVOID  Unknown0x12c;
/*0130*/ LPVOID  PostDraw8;
/*0134*/
} CXWNDVFTABLE, *PCXWNDVFTABLE;

// Lax
// Actual size 0x148 9-5-2007             
typedef struct _CXWND {
/*0x000*/   struct  _CXWNDVFTABLE   *pvfTable;
/*0x004*/   DWORD   ZLayer;
/*0x008*/   BYTE    HasChildren;
/*0x009*/   BYTE    Unknown0x9[0xf];
/*0x018*/   BYTE    Alpha;
/*0x019*/   BYTE    Unknown0x19[0x17];
/*0x030*/   BYTE    ValidCXWnd;
/*0x031*/   BYTE    Unknown0x31[0x3];
/*0x034*/   struct  _CSIDLWND *pSiblings;
/*0x038*/   BYTE    HasSiblings;
/*0x039*/   BYTE    Unknown0x39[0x3];
/*0x03c*/   LPVOID  DrawTemplate;
/*0x040*/   BYTE    Unlockable;     // related to Locked
/*0x041*/   BYTE    Unknown0x41;
/*0x042*/   BYTE    CloseOnESC;     // close when ESC is pressed
/*0x043*/   BYTE    Unknown0x43[0x5];
/*0x048*/   DWORD   HScrollPos;
/*0x04c*/   BYTE    Unknown0x4c[0x4];
/*0x050*/   BYTE    Clickable;      // found in the button handlers
/*0x051*/   BYTE    Unknown0x51[0x3];
/*0x054*/   DWORD   WindowStyle;    // bit 1 - vertical scroll, bit 2 - horizontal scroll, bit 4 - title bar?, bit 8 - border
/*0x058*/   struct  _CXSTR  *XMLToolTip;
/*0x05c*/   BYTE    FadeToAlpha;
/*0x05d*/   BYTE    Unknown0x60[0x7];
/*0x064*/   DWORD   TimeMouseOver;
/*0x068*/   BYTE    Unknown0x68[0x8];
/*0x070*/   BYTE    Enabled;
/*0x071*/   BYTE    Unknown0x71[0x3];
/*0x074*/   struct  _CXSTR  *Tooltip;
/*0x078*/   BYTE    Unknown0x78;
/*0x079*/   BYTE    MouseOver;
/*0x07a*/   BYTE    Minimized;
/*0x07b*/   BYTE    Unknown0x7b[0xd];
/*0x088*/   struct  _CSIDLWND *pChildren;
/*0x08c*/   BYTE    Unknown0x8c;
/*0x08d*/   BYTE    Show;
/*0x08e*/   BYTE    Unknown0x8e[0x2];
/*0x090*/   DWORD   VScrollMax;
/*0x094*/   BYTE    Locked;
/*0x095*/   BYTE    Faded;
/*0x096*/   BYTE    Unknown0x96[0x6];
/*0x09c*/   struct  _CXSTR  *WindowText;
/*0x0a0*/   BYTE    Unknown0xa0[0xc];
/*0x0ac*/   RECT    OldLocation;
/*0x0bc*/   BYTE    Unknown0xbc[0xc];
/*0x0c8*/   DWORD   BGType;         // "BGType" in ini
/*0x0cc*/   ARGBCOLOR BGColor;
/*0x0d0*/   BYTE    Unknown0xd0[0x24];
/*0x0f4*/   DWORD   UnknownCW;
/*0x0f8*/   DWORD   XMLIndex;
/*0x0fc*/   BYTE    Unknown0xfc[0x4];
/*0x100*/   struct  _CSIDLWND *pParentWindow; // If this is NULL, coordinates are absolute...
/*0x104*/   BYTE    Unknown0x104[0xc];
/*0x110*/   DWORD   VScrollPos;
/*0x114*/   DWORD   TickCount2;
/*0x118*/   BYTE    Fades;
/*0x119*/   BYTE    Unknown0x119[0x3];
/*0x11c*/   DWORD   FadeDuration;
/*0x120*/   RECT    Location;
/*0x130*/   DWORD   HScrollMax;
/*0x134*/   BYTE    Unknown0x134[0x10];
/*0x144*/   struct  _CXSTR  *SidlText;
/*0x148*/
} CXWND, *PCXWND;
#define GateBind          0


// Actual size 0x178 8-14-07
typedef struct _CSIDLWND {
/*0x000*/   struct  _CSIDLWNDVFTABLE   *pvfTable;
/*0x004*/   DWORD   ZLayer;
/*0x008*/   BYTE    HasChildren;
/*0x009*/   BYTE    Unknown0x9[0xf];
/*0x018*/   BYTE    Alpha;
/*0x019*/   BYTE    Unknown0x19[0x17];
/*0x030*/   BYTE    ValidCXWnd;
/*0x031*/   BYTE    Unknown0x31[0x3];
/*0x034*/   struct  _CSIDLWND *pSiblings;
/*0x038*/   BYTE    HasSiblings;
/*0x039*/   BYTE    Unknown0x39[0x3];
/*0x03c*/   LPVOID  DrawTemplate;
/*0x040*/   BYTE    Unlockable;     // related to Locked
/*0x041*/   BYTE    Unknown0x41;
/*0x042*/   BYTE    CloseOnESC;     // close when ESC is pressed
/*0x043*/   BYTE    Unknown0x43[0x5];
/*0x048*/   DWORD   HScrollPos;
/*0x04c*/   BYTE    Unknown0x4c[0x4];
/*0x050*/   BYTE    Clickable;      // found in the button handlers
/*0x051*/   BYTE    Unknown0x51[0x3];
/*0x054*/   DWORD   WindowStyle;    // bit 1 - vertical scroll, bit 2 - horizontal scroll, bit 4 - title bar?, bit 8 - border
/*0x058*/   struct  _CXSTR  *XMLToolTip;
/*0x05c*/   BYTE    FadeToAlpha;
/*0x05d*/   BYTE    Unknown0x60[0x7];
/*0x064*/   DWORD   TimeMouseOver;
/*0x068*/   BYTE    Unknown0x68[0x8];
/*0x070*/   BYTE    Enabled;
/*0x071*/   BYTE    Unknown0x71[0x3];
/*0x074*/   struct  _CXSTR  *Tooltip;
/*0x078*/   BYTE    Unknown0x78;
/*0x079*/   BYTE    MouseOver;
/*0x07a*/   BYTE    Minimized;
/*0x07b*/   BYTE    Unknown0x7b[0xd];
/*0x088*/   struct  _CSIDLWND *pChildren;
/*0x08c*/   BYTE    Unknown0x8c;
/*0x08d*/   BYTE    Show;
/*0x08e*/   BYTE    Unknown0x8e[0x2];
/*0x090*/   DWORD   VScrollMax;
/*0x094*/   BYTE    Locked;
/*0x095*/   BYTE    Faded;
/*0x096*/   BYTE    Unknown0x96[0x6];
/*0x09c*/   struct  _CXSTR  *WindowText;
/*0x0a0*/   BYTE    Unknown0xa0[0xc];
/*0x0ac*/   RECT    OldLocation;
/*0x0bc*/   BYTE    Unknown0xbc[0xc];
/*0x0c8*/   DWORD   BGType;         // "BGType" in ini
/*0x0cc*/   ARGBCOLOR BGColor;
/*0x0d0*/   BYTE    Unknown0xd0[0x24];
/*0x0f4*/   DWORD   UnknownCW;
/*0x0f8*/   DWORD   XMLIndex;
/*0x0fc*/   BYTE    Unknown0xfc[0x4];
/*0x100*/   struct  _CSIDLWND *pParentWindow; // If this is NULL, coordinates are absolute...
/*0x104*/   BYTE    Unknown0x104[0xc];
/*0x110*/   DWORD   VScrollPos;
/*0x114*/   DWORD   TickCount2;
/*0x118*/   BYTE    Fades;
/*0x119*/   BYTE    Unknown0x119[0x3];
/*0x11c*/   DWORD   FadeDuration;
/*0x120*/   RECT    Location;
/*0x130*/   DWORD   HScrollMax;
/*0x134*/   BYTE    Unknown0x134[0x10];
/*0x144*/   union {
                struct _CXSTR  *SidlText;
                DWORD  Items;
            };
/*0x148*/   union {
                struct _CXSTR  *SidlScreen;
                DWORD   SlotID;
            };
/*0x14c*/   LPVOID  SidlPiece;   // CScreenPieceTemplate (important)
/*0x150*/   BYTE    Checked;
/*0x151*/   BYTE    Highlighted;
/*0x152*/   BYTE    Unused0x146[0x2];
/*0x154*/   DWORD   TextureAnim; // used in CSidlScreenWnd::AddButtonToRadioGroup
/*0x158*/   struct  _CXSTR *   InputText;
/*0x15c*/   DWORD   Selector;
/*0x160*/   DWORD   PushToSelector;
/*0x164*/   DWORD   EnableINIStorage;
/*0x168*/   union {
                struct _CXSTR *INIStorageName;
                struct _EQINVSLOT *pEQInvSlot;
            };
/*0x16c*/   DWORD   Unknown0x16c;
/*0x170*/   DWORD   Unknown0x170;// CTextureAnimation
/*0x174*/   DWORD   Unknown0x174;// CTextureAnimation
/*0x178*/   BYTE    Unknown0x178[0x4];
/*0x17c*/   LPVOID  ContextMenu; // CTextureAnimation
/*0x180*/   DWORD   Unknown0x180;// CTextureAnimation
/*0x184*/   DWORD   Unknown0x184;// CTextureAnimation
/*0x188*/
} CSIDLWND, *PCSIDLWND;

// size 0x26c  02-13-2007
typedef struct _CSIDLMGR {
/*0x000*/	BYTE	Unknown0x000[0x118];
/*0x118*/	PVOID pXMLDataMgr;
/*0x11c*/	BYTE	Unknown0x11c[0x150];
/*0x26c*/
} CSIDLMGR, *PCSIDLMGR;

//11-6-2003 lax
//Actual size 0x108 4-28-2004
//Actual size 0x108 4-12-2005
typedef struct _CXWNDMGR {
/*0x000*/ LPVOID CXWNDMGR__vtable;
/*0x004*/ DWORD Unknown0x004;
/*0x008*/ struct _CSIDLWND *pWindows;
/*0x00c*/ BYTE Unknown0x00C[0x71];
/*0x07d*/ bool KeyboardFlags[4];  // 7d-80
/*0x081*/ CHAR Unknown0x081[0x6b];
/*0x0EC*/ DWORD ScreenExtentX;
/*0x0F0*/ DWORD ScreenExtentY;
/*0x0F4*/ DWORD Unknown;
/*0x0F8*/ DWORD font_list_ptr;
/*0x0Fc*/ BYTE Unknown0xfc[0xc];
/*0x108*/ 
} CXWNDMGR, *PCXWNDMGR;


// 06-16-2004 dkaa - size is 0x404
// 05-13-2004 dkaa - size is 0x2d4
typedef struct _EQMERCHWINDOW {
/*0x000*/   struct _CSIDLWND Wnd;
/*0x15c*/   BYTE    Unknown0x150[0x8];
/*0x164*/   PCONTENTS   ItemDesc[0x50];     // the mainwindow has pointers
                                            // directly to the items in the
                                            // slots...
/*0x2a4*/   FLOAT   Markup;
/*0x2a8*/   DWORD   SelectedSlotID;
/*0x2ac*/   DWORD   AddressToPointerForSelectedItem;
/*0x2b0*/   DWORD   Unknown0x2b0;
/*0x2b4*/   DWORD   MW_MerchantName;
/*0x2b8*/   DWORD   MW_SelectedItemLabel;
/*0x2bc*/   DWORD   MW_SelectedPriceLabel;
/*0x2c0*/   DWORD   MW_SelectedItem;
/*0x2c4*/   DWORD   MW_Buy_Button;
/*0x2c8*/   DWORD   MW_Sell_Button;
/*0x2cc*/   DWORD   ItemList;
/*0x2d0*/   DWORD   Unknown0x2d0;
/*0x2d4*/
} EQMERCHWINDOW, *PEQMERCHWINDOW;



// 10-27-2003 Lax
// Actual size 0x208 11-5-2003
typedef struct _EQCHATMGR
{
/*0x000*/ struct _EQCHATWINDOW* ChatWnd[0x20];
/*0x080*/ DWORD NumWindows;
/*0x084*/ DWORD ActiveWindow; // CChat::GetActiveChatWindow
/*0x088*/ DWORD LockedWindow; // CChatManager::GetActiveChatWindow
/*0x08c*/ DWORD Unknown0x08c;
/*0x090*/ struct _EQCHATWINDOW* ChannelMap[0x20];   // channel map
/*0x110*/ BYTE  Unknown0x110[0x28];
/*0x138*/ DWORD Unknown0x138;
/*0x13c*/ DWORD Unknown0x13c;
/*0x140*/ DWORD Unknown0x140;
/*0x144*/ LPVOID ChatMenu;
/*0x148*/ DWORD Unknown0x148;
/*0x14c*/ DWORD Unknown0x14c;
/*0x150*/ DWORD Unknown0x150;
/*0x154*/ DWORD Unknown0x154;
/*0x158*/ LPVOID MeleeMenu;
/*0x15c*/ DWORD Unknown0x15c;
/*0x160*/ LPVOID SpellMenu;
/*0x164*/ DWORD Unknown0x164;
/*0x168*/ DWORD Unknown0x168;
/*0x16c*/ LPVOID ChannelMenu;
/*0x170*/ LPVOID ChatMenu2;
/*0x174*/ LPVOID FilterMenu;
/*0x178*/ DWORD Unknown0x178;
/*0x17c*/ DWORD Unknown0x17c;
/*0x180*/ DWORD Unknown0x180;
/*0x184*/ LPVOID HitsModeMenu;
/*0x188*/ LPVOID MeleeMenu2;
/*0x18c*/ LPVOID HitsModeMenu2;
/*0x190*/ DWORD Unknown0x190;
/*0x194*/ LPVOID HitsModeMenu3;
/*0x198*/ DWORD Unknown0x198;
/*0x19c*/ LPVOID HitsModeMenu4;
/*0x1a0*/ DWORD Unknown0x1a0;
/*0x1a4*/ LPVOID HitsModeMenu5;
/*0x1a8*/ DWORD Unknown0x1a8;
/*0x1ac*/ LPVOID HitsModeMenu6;
/*0x1b0*/ DWORD Unknown0x1b0;
/*0x1b4*/ DWORD Unknown0x1b4; // CChatManager__UpdateTellMenus
/*0x1b8*/ DWORD Unknown0x1b8;
/*0x1bc*/ DWORD Unknown0x1bc; // CChatManager__UpdateTellMenus
/*0x1c0*/ DWORD Unknown0x1c0;
/*0x1c4*/ DWORD Unknown0x1c4; // CChatManager__UpdateTellMenus // raid
/*0x1c8*/ DWORD Unknown0x1c8;
/*0x1cc*/ DWORD Unknown0x1cc; // CChatManager__UpdateTellMenus
/*0x1d0*/ DWORD Unknown0x1d0; // CChatManager__UpdateTellMenus
/*0x1d4*/ DWORD Unknown0x1d4; // CChatManager__UpdateTellMenus
/*0x1d8*/ DWORD Unknown0x1d8;
/*0x1dc*/ DWORD Unknown0x1dc;
/*0x1e0*/ DWORD Unknown0x1e0;
/*0x1e4*/ BYTE  Unknown0x1e4[0x1c];
/*0x200*/ DWORD Unknown0x200;
/*0x204*/ DWORD Unknown0x204;
/*0x208*/
} EQCHATMGR, *PEQCHATMGR;

// Actual size 0x26c 02-14-2007
// 10-27-2003 Lax
typedef struct _EQCHATWINDOW
{
/*0x000*/ struct _CSIDLWND Wnd;
/*0x160*/ struct _EQCHATMGR *ChatManager; 
/*0x164*/ struct _CSIDLWND* InputWnd;
/*0x168*/ struct _CSIDLWND* OutputWnd;
/*0x16c*/ DWORD Unknown0x154;
/*0x170*/ DWORD Unknown0x158;// need to update locations..
/*0x174*/ BYTE Unknown0x148;
/*0x175*/ BYTE Unknown0x149[0x3f];
/*0x1b4*/ DWORD Unknown0x188;
/*0x1b8*/ DWORD Unknown0x18c;
/*0x1bc*/ struct _CXSTR *CommandHistory[0x28]; // ->0x198
/*0x25c*/ DWORD Unknown0x230; // CChatWindow::HistoryBack/forward .. maybe total or current history lines
/*0x260*/ DWORD Unknown0x234; // CChatWindow::HistoryBack/forward .. maybe total or current history lines
/*0x264*/ DWORD FontSize; //
/*0x268*/ DWORD Unknown0x23C;
/*0x26c*/
} EQCHATWINDOW, *PEQCHATWINDOW;

// Actual size 0x1dc old
// Lax 10-24-2003
typedef struct _EQBUFFWINDOW
{
/*0x000*/ struct _CSIDLWND Wnd;
/*0x148*/ BYTE  Unknown0x148[0x48];
/*0x184*/ DWORD pBuff[0x0f];    // CButton*
/*0x1C0*/ DWORD Unknown0x1C0;
/*0x1C4*/ DWORD Unknown0x1C4;
/*0x1C8*/ DWORD Unknown0x1C8;
/*0x1CC*/ DWORD Unknown0x1CC;
/*0x1D0*/ DWORD FirstEffectSlot; //0-15
/*0x1D4*/ DWORD LastEffectSlot;  //0-15
/*0x1D8*/ DWORD MaxBuffs;  // either 0x0F or 0x06  (normal or shortbuffs)
/*0x1DC*/
} EQBUFFWINDOW, *PEQBUFFWINDOW;

// actual size 0x148
typedef struct _EQINVSLOTWND {
/*0x000*/   struct _CXWND Wnd;
/*0x110*/	LONG InvSlot;
/*0x114*/   BYTE Unknown0x114[0x28];
/*0x13C*/   BOOL ProcessClick;
} EQINVSLOTWND, *PEQINVSLOTWND;

// actual size 0x14 2-18-2004 Lax
typedef struct _EQINVSLOT {
/*0x00*/	LPVOID pvfTable; // not based on cxwnd
/*0x04*/	struct _EQINVSLOTWND *pInvSlotWnd;
/*0x08*/	DWORD Unknown0x08;
/*0x0C*/	DWORD Unknown0x0C;
/*0x10*/	PCONTENTS *ppContents;
/*0x14*/
} EQINVSLOT, *PEQINVSLOT;

// actual size 0x100C 2-18-2004 Lax
typedef struct _EQINVSLOTMGR {
/*0x0000*/	DWORD Unknown0x0000;
/*0x0004*/	struct _EQINVSLOT *SlotArray[0x400];
/*0x1004*/	DWORD TotalSlots;
/*0x1008*/	DWORD Unknown0x1008;
/*0x100C*/
} EQINVSLOTMGR, *PEQINVSLOTMGR;


// onetimehero 09-17-03
// ContainerWindow
// Actual Size 0x17C old
typedef struct _EQCONTAINERWINDOW {
/*0x000*/ struct _CSIDLWND Wnd;
/*0x148*/ struct _CONTENTS*   pContents;     // Pointer to the contents of the container;
                                                 // Matches the pointer in CHARINFO.Inventory/Bank/World
/*0x14c*/ struct _CSIDLWND*   pSlots[0x0a];
/*0x000*/ struct _CSIDLWND*   pCombine;
/*0x168*/ struct _CSIDLWND*   pDone;
/*0x16c*/ struct _CSIDLWND*   pIcon;
/*0x170*/ struct _CSIDLWND*   pUnknown;
/*0x174*/ struct _CSIDLWND*   pLabel;
/*0x178*/ BYTE Unknown0x178[4];
/*0x17c*/
} EQCONTAINERWINDOW, *PEQCONTAINERWINDOW;

// Actual Size 0x98  03/15/06
typedef struct _EQ_CONTAINERWND_MANAGER {
/*0x000*/   DWORD pvfTable; // NOT based on CXWnd.  Contains only destructor
/*0x004*/   PEQCONTAINERWINDOW pPCContainers[0x22];  // All open containers, including World, in order of opening...
/*0x08c*/   PCONTENTS   pWorldContents;            // Pointer to the contents of the world   If NULL, world container isn't open;
/*0x090*/   DWORD dwWorldContainerID;            // ID of container in zone, starts at one (zero?) and goes up.
/*0x094*/   DWORD dwTimeSpentWithWorldContainerOpen;  // Cumulative counter?
/*0x078*/
} EQ_CONTAINERWND_MANAGER, *PEQ_CONTAINERWND_MANAGER;

typedef struct _POINT3 {
    FLOAT X;
    FLOAT Y;
    FLOAT Z;
} POINT3, *PPOINT3;

// plazmic 08-17-03
// Map Window
typedef struct _MAPLABEL { // sizeof() = 0x28
/*0x00*/    struct _MAPLABEL *pNext;
/*0x04*/    struct _MAPLABEL *pPrev;
/*0x08*/    POINT3 Location;
/*0x14*/    ARGBCOLOR Color;
/*0x18*/    DWORD Size; //1-3;
/*0x1C*/    PCHAR Label;
/*0x20*/    DWORD Layer;    //0-3;
/*0x24*/    DWORD Width;
/*0x28*/    DWORD Height;
/*0x2C*/    DWORD unk_0x2c;
/*0x30*/    DWORD unk_0x30;
} MAPLABEL, *PMAPLABEL;

typedef struct _MAPLINE { // sizeof() = 0x28 (think this might be 0x34 now)
    struct _MAPLINE *pNext;
    struct _MAPLINE *pPrev;
    POINT3 Start;
    POINT3 End;
    ARGBCOLOR Color;
    DWORD Layer;    //0-3;
} MAPLINE, *PMAPLINE;

typedef struct _EQLOOTWINDOW {
/*0x000*/ struct _CSIDLWND Wnd;
/*0x160*/ BYTE  Unknown0x160[0x90];
/*0x1f0*/ PCONTENTS   ItemDesc[NUM_INV_SLOTS]; //there can only be 31 items on a corpse since that equals 23 inv slots plus 8 bags...
} EQLOOTWINDOW, *PEQLOOTWINDOW;

//Size: 0x3a4 (03/25/2004)
typedef struct _EQPETINFOWINDOW {
/*0x000*/ struct _CSIDLWND Wnd;
/*0x174*/ DWORD Unknown0x174;
/*0x178*/ DWORD Unknown0x178; // pet's spawnid
/*0x17c*/ DWORD Unknown0x17c; // bool
/*0x180*/ BYTE  Unknown0x180[0x120];
/* buttons are here */
/*0x2a0*/ struct _CXWND *wnd;           // spell info wnd?
/*0x2a4*/ int   Buff[0x1e]; // Spell ID# of each buff -- 30 total
/*0x31c*/ DWORD Unknown0x318;  
/*0x320*/ BYTE  Unknown0x31c[4];
/*0x324*/ DWORD Unknown0x320[2];
/*0x32c*/ DWORD BuffFadeETA[0x1e]; // duration until buff fades, in thousands of a second
} EQPETINFOWINDOW, *PEQPETINFOWINDOW;

typedef struct _EQTRADESKILLRECIPE {
/*0x00*/ DWORD Unknown0x00;
/*0x04*/ DWORD Trivial;
/*0x08*/ DWORD Unknown0x08;
/*0x0c*/ DWORD Unknown0x0c;
/*0x10*/ DWORD Unknown0x10;
/*0x14*/ CHAR  Name[0x40];
/*0x54*/ DWORD Unknown0x54;
/*0x58*/ DWORD Ingredient[0xa];  // ID# of ingredient(s)
/*0x80*/ DWORD IngredientIcon[0xa]; //Icon# of ingredient(s) (Note: 499 is blank)
} EQTRADESKILLRECIPE, *PEQTRADESKILLRECIPE;

//Size 0x3d8 (02/10/2004)
typedef struct _EQTRADESKILLWINDOW {
/*0x000*/   struct _CSIDLWND Wnd;
/*0x148*/ DWORD Unknown0x148[0x1b];  
/*0x1b4*/ PEQTRADESKILLRECIPE SearchResults[0x64]; 
/*0x344*/ DWORD	Unknown0x344;
/*0x348*/ DWORD Unknown0x348;
/*0x34c*/ DWORD Unknown0x34c;
/*0x350*/ PCONTENTS Container;
/*0x354*/ DWORD Unknown0x354;
/*0x358*/ PEQTRADESKILLRECIPE SelectedRecipe;
/*0x35c*/ DWORD Unknown0x35c;
/*0x360*/ DWORD SkillLevel;
/*0x364*/ DWORD Unknown0x364;
/*0x368*/ DWORD Unknown0x368;
/*0x36c*/ BYTE  Unknown0x36c[0x58];
/*0x3c4*/ DWORD Unknown0x3c4;
/*0x3c8*/ DWORD Unknown0x3c8;
/*0x3cc*/ BYTE  Unknonw0x3cc[0x8];
/*0x3d4*/ DWORD Unknown0x3d4;
/*0x3d8*/ DWORD Unknown0x3d8;
} EQTRADESKILLWINDOW, *PTRADESKILLWINDOW;

//Actual Size 0x238 - 02/08/06 - ieatacid
typedef struct _EQTRADEWINDOW {
/*0x000*/ struct _CSIDLWND Wnd;
/*0x15c*/ BYTE   Unknown0x15c[0xd8];
/*0x234*/ BYTE   HisTradeReady;
/*0x235*/ BYTE   MyTradeReady;
/*0x236*/ BYTE   TradeWndOpen;
/*0x237*/ BYTE   Unknown0x236;
/*0x238*/
} EQTRADEWINDOW, *PTRADEWINDOW; 

//Size: 0x28c (08/11/2004) 
typedef struct _EQRAIDWINDOW {
/*0x000*/ struct _CSIDLWND Wnd;
/*0x158*/ BYTE  Unknown0x158[0xa4];
/*0x1fc*/ DWORD ClassColors[0x10];
/*0x23c*/
} EQRAIDWINDOW, *PEQRAIDWINDOW;

typedef struct _GUILDMEMBERINFO {
/*0x000*/ DWORD Unknown0x000; //pointer?
/*0x004*/ BYTE OnlineStatus; // 00=offline, 01=online
/*0x005*/ BYTE UnknownData0x005;
/*0x006*/ BYTE UnknownData0x006;
/*0x007*/ BYTE Unknown0x007;
/*0x008*/ BYTE Unknown0x008[0x8];
/*0x010*/ CHAR Name[0x40];
/*0x050*/ DWORD Level;
/*0x054*/ DWORD Class; 
/*0x058*/ DWORD Rank; //0=member, 1=officer, 2=leader
/*0x05c*/ FLOAT UnknownData0x05c;
/*0x060*/ CHAR PublicNote[0x80];
/*0x0e0*/ BYTE Unknown0x0e0[0x180];
/*0x260*/ WORD UnknownData0x260;
/*0x262*/ WORD UnknownData0x262;
/*0x264*/ WORD UnknownData0x264;
/*0x266*/ WORD UnknownData0x266;
} GUILDMEMBERINFO, *PGUILDMEMBERINFO;

//Size: 0x3d0 (02/18/2004)
typedef struct _EQGUILDWINDOW {
/*0x000*/ struct _CSIDLWND Wnd;
/*0x148*/ BYTE  Unknown0x148[0x4c];
/*0x194*/ BYTE Unknown0x194;
/*0x195*/ BYTE ShowOffline; // 01 = show offline box is checked
/*0x196*/ BYTE Unknown0x196[0x2];
/*0x198*/ struct _GUILDMEMBERINFO **pMember; // This is a pointer to the beginning of a list of pointers, 
	                                         // each representing one player in the guild.  The number of 
	                                         // pointers depends upon TotalMemberCount.
/*0x19c*/ DWORD TotalMemberCount;
/*0x1a0*/ DWORD Unknown0x1a0;  // 150?
/*0x1a4*/ DWORD Unknown0x1a4;  // 1?
/*0x1a8*/ DWORD TotalMemberCountAgain;
/*0x1ac*/ BYTE Unknown0x1ac[0x1c];
/*0x1c8*/ CHAR PersonalNotesFilePath[0x40];  // path to personal notes file
/*0x208*/ BYTE Unknown0x208[0x1c0]; // empty
/*0x3c8*/ DWORD Unknown0x3c8; // 1?
/*0x3cc*/ DWORD Unknown0x3cc; // some sort of bizaare pointer...
/*0x3d0*/
} EQGUILDWINDOW, *PEQGUILDWINDOW;

typedef struct _EQNOTESWINDOW {
/*0x000*/ struct _CSIDLWND Wnd;
/*0x148*/ BYTE  Unknown0x148[0x04];
/*0x14c*/ struct _CSIDLWND *pEditWnd;
} EQNOTESWINDOW, *PEQNOTESWINDOW;

// Lax 10-29-2003 
// Actual size 0x4e8 07-05-2007
typedef struct _EQITEMWINDOW 
{ 
/*0x000*/ struct _CSIDLWND Wnd;
/*0x180*/ struct _CSIDLWND *DisplayWnd;
/*0x184*/ BYTE Unknown0x184[0x18];
/*0x19c*/ PCXSTR ItemInfo;
/*0x1a0*/ PCXSTR WindowTitle;
/*0x1a4*/ DWORD Unknown0x1a4; // possibly PCXSTR of information as on charms 
/*0x1a8*/ DWORD Unknown0x1a8;
/*0x1ac*/ DWORD Unknown0x1ac;
/*0x1b0*/ PCONTENTS pItem;
/*0x1b4*/ PVOID TextureAnim;
/*0x1b8*/ PVOID TextureAnim2;
/*0x1bc*/ BYTE  Unknown0x1bc[0x50];
/*0x1c0*/ DWORD IDW_ItemInfo_Num_Lines;
/*0x210*/ struct _CSIDLWND *IDW_ItemInfo[0xc];
/*0x240*/ // pointers to stat labels, etc, in this area
} EQITEMWINDOW, *PEQITEMWINDOW;

// Actual size 0x19c 08-17-2006
typedef struct _EQSPELLINFOWINDOW 
{ 
/*0x000*/ struct _CSIDLWND Wnd; 
/*0x174*/ struct _CSIDLWND *DisplayWnd;
/*0x178*/ BYTE Unknown0x178[0x4]; 
/*0x17c*/ BYTE Unknown0x17c; 
/*0x17d*/ BYTE Unknown0x17d[0x3]; 
/*0x180*/ PCXSTR ItemInfo;
/*0x184*/ PVOID Unknown0x194;
/*0x188*/ DWORD Unknown0x198;
/*0x18c*/ DWORD Unknown0x19c;
/*0x190*/ DWORD Unknown0x1a0;
/*0x194*/ DWORD Unknown0x1a4;
/*0x198*/ PVOID TextureAnim;
/*0x19c*/ 
} EQSPELLINFOWINDOW, *PEQSPELLINFOWINDOW;

// Size: 0x1a4 (4/14/2005)
typedef struct _EQMAILWINDOW {
/*0x000*/ struct _CSIDLWND Wnd;
/*0x158  Finish... */
} EQMAILWINDOW, *PEQMAILWINDOW;


// Actual Size 0x2d4 12-07-2005
typedef struct _EQMAPWINDOW {
/*0x000*/ struct _CSIDLWND Wnd;
/*0x174*/ BYTE Unknown0x174[0x28];
/*0x198*/ CHAR shortzonename[0x20]; 
/*0x1b8*/ BYTE Unknown0x1b8[0x9c];
/*0x254*/ struct _CXWND *wnd;           // map wnd?
/*0x258*/ BYTE Unknown0x258[0xc];
/*0x264*/ BYTE MapViewMapStart[0x40];
/*0x2a4*/ PMAPLINE pLines;       
/*0x2a8*/ PMAPLABEL pLabels;			
/*0x294*/ BYTE Unknown0x294[0x34];
/*0x2c8*/ DWORD ContentsCount;  //number of lines/labels on the map (I think)
/*0x2d4*/
} EQMAPWINDOW, *PEQMAPWINDOW;

// dkaa 02-14-07
// Spell Window
typedef struct _EQCASTSPELLWINDOW {
/*0x000*/ struct _CSIDLWND Wnd;
/*0x188*/ BYTE Unknown0x148[0x0c];
/*0x194*/  struct _EQCASTSPELLGEM   *SpellSlots[0x9];
/*0x1xx*/  BYTE    Unknown0x190[0x30];
/*0x1c0*/
} EQCASTSPELLWINDOW, *PEQCASTSPELLWINDOW;

// Individual Gems 
typedef struct _EQCASTSPELLGEM { 
/*0x000*/ struct	_CSIDLWND Wnd; 
/*0x188*/ BYTE		Unknown0x188[0x04]; 
/*0x18c*/ BYTE		Unknown0x18c[0x20]; 
/*0x1ac*/ BYTE		Unknown0x1ac[0x4]; 
/*0x1b0*/ DWORD		spellicon;//if this is equal to FFFFFFFF there is no spell memmed in this slot... 
/*0x1b4*/ DWORD		spellstate;// 1 = cast in progress or refreshtime not met 2 means we ducked or aborted cast, 0 means its ok to cast 
/*0x1b8*/ BYTE          Unknown0x194[0x18];
/*0x1ac*/ 
} EQCASTSPELLGEM, *PEQCASTSPELLGEM;
#define Fly					 0

// Actual size 0x1c4 10-9-2003
typedef struct _EQHOTBUTTONWINDOW {
/*0x000*/ struct _CSIDLWND Wnd;
/*0x148*/ BYTE Unknown0x138[0xc];
/*0x000*/ DWORD   HotButtonWndView;//0 to 9 for the different views
/*0x14c*/ struct _CSIDLWND   *HotButtons[0x0a];//these will change when you switch page...
/*0x174*/
} EQHOTBUTTONWINDOW, *PEQHOTBUTTONWINDOW;

typedef struct _LOOTCORPSE {
/*0x000*/   BYTE    Unknown0x000;   // 03 seems very common (for NPC anyway)
/*0x001*/   CHAR    Name[0x4b];
/*0x04c*/   FLOAT   Timer;  //Uncertain the equation used here, but fairly certain it's the timer
/*0x050*/   FLOAT   X;
/*0x054*/   BYTE    Unknown0x054[0x4];
/*0x058*/   FLOAT   Y;
/*0x05c*/   FLOAT   Z;
/*0x060*/   BYTE    Unknown0x060[0x24];
/*0x084*/   DWORD   Flags;
/*0x088*/   BYTE    Unknown0x088[0x4];
/*0x08c*/   DWORD   Unknown0x08c;   //256 seems very common
/*0x090*/
// I think there might be pointers at 0x0e0..but no clue what to
} LOOTCORPSE, *PLOOTCORPSE;

// size 0x180 3-10-2004
typedef struct _EQCOMPASSWINDOW {
/*0x000*/	struct _CSIDLWND Wnd;
/*0x148*/	struct _CSIDLWND *pStrip1;
/*0x14C*/	struct _CSIDLWND *pStrip2;
// in progress.
/*0x16C*/   BOOL DrawLine;
/*0x170*/   DWORD Unknown0x170;
/*0x174*/	DWORD LineRed;
/*0x178*/	DWORD LineGreen;
/*0x17C*/	DWORD LineBlue;
/*0x180*/
} EQCOMPASSWINDOW, *PEQCOMPASSWINDOW;

// used by options window
typedef struct _EQKBASSIGN
{
/*0x00*/	struct _CXSTR *pDescription;
/*0x04*/	DWORD nAssignmentNumber;
/*0x08*/
} EQKBASSIGN, *PEQKBASSIGN;

// size 0x904 3-10-2004
typedef struct _EQOPTIONSWINDOW {
/*0x000*/	struct _CSIDLWND Wnd;
/*0x148*/

/*0x264*/ struct _EQKBASSIGN Binds[0xA1];
/*0x508*/

/*0x904*/
} EQOPTIONSWINDOW, *PEQOPTIONSWINDOW;

// size 0x28 4-24-2004 Lax
typedef struct _EQGRAPHICSENGINE {
/*0x00*/ // Graphics file handler
/*0x04*/
} EQGRAPHICSENGINE, *PEQGRAPHICSENGINE;
#define SafeXLoc 0

typedef struct _CBUTTONWND {
/*0x000*/ struct    _CXWND Wnd;
/*0x138*/ BYTE      Unknown[0x8];
/*0x140*/ BYTE      State;        //1=down 0=up
/*0x141*/ BYTE      Unknown0x141; //something to do with State
/*0x142*/ BYTE      Unknown0x142[0x12];
/*0x154*/ ARGBCOLOR Color;
/*0x158*/
} CBUTTONWND, *PCBUTTONWND;
 
typedef struct _CTEXTENTRYWND {
/*0x000*/ struct    _CXWND Wnd;
/*0x138*/ DWORD     CursorPos1;
/*0x13c*/ DWORD     CursorPos2; //same as above but only this needs to be set to update cursor position
/*0x140*/
} CTEXTENTRYWND, *PCTEXTENTRYWND;

// size 0x1b4 10/04/07 dkaa
typedef struct _CPLAYERWND {
/*0x000*/ struct _CSIDLWND Wnd;
/*0x188*/ BYTE   Unknown[0x28];
/*0x1b0*/ DWORD  CombatState; // 1=debuffed, 2=combat cooldown, 3=stand, 4=sit
/*0x1b4*/ 
} CPLAYERWND, *PCPLAYERWND;

};


using namespace EQUIStructs;
