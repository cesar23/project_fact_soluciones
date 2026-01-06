#!/bin/bash
# 🎗️🎖️⛏️⚒️🛠️⚙️🏷️🗂️⏱️🛸⛱️❤️‼️✔️☢️☣️⚠️🗓️🧱🧬🪛🗝️🕹️🎗️✌️👁️ℹ️Ⓜ️☄️🌨️🌩️🌦️🌤️🏚️
#  ver ccolores en repo linux => Colores/1_generar_colores.sh
#  \e[0m == \033[0m
# Reset
Color_Off='\e[0m'       # Text Reset
#Color_Off='\033[1;37m'       # Text Reset
Color_Reset_full=`echo -en "\e[0m"`

#Color_Off='\033[0m'       # Text Reset


# ---------------------------------------------------------------
# ------ 🏷️ Colores que afectan la linea actual ----------------
# ---------------------------------------------------------------
#View https://drive.google.com/file/d/1wE-EMdnllGndRrUqYESa4eQX5ZcsVqXZ/view?usp=drivesdk


# ✔️:::::::::  ejemplo Uso:
#      echo -en " -- ---- * command ${Red}:  ver menu ${Color_Off} favoritos \n"

# Regular Colors
Black='\e[0;30m'        # Black
Red='\e[0;31m'          # Red #a9222a
Green='\e[0;32m'        # Green
Yellow='\e[0;33m'       # Yellow
Blue='\e[0;34m'         # Blue
Purple='\e[0;35m'       # Purple
Cyan='\e[0;36m'         # Cyan
White='\e[0;37m'        # White
Gray='\e[1;30m'        # Gray

# Bold
BBlack='\e[1;30m'       # Black (Bold)
BRed='\e[1;31m'         # Red (Bold)
BGreen='\e[1;32m'       # Green (Bold)
BYellow='\e[1;33m'      # Yellow (Bold)
BBlue='\e[1;34m'        # Blue (Bold)
BPurple='\e[1;35m'      # Purple (Bold)
BCyan='\e[1;36m'        # Cyan (Bold)
BWhite='\e[1;37m'       # White (Bold)
BGray='\e[1;90m'        # Gray  (Bold)

# Underline
UBlack='\e[4;30m'       # Black  (Underline)
URed='\e[4;31m'         # Red  (Underline)
UGreen='\e[4;32m'       # Green  (Underline)
UYellow='\e[4;33m'      # Yellow  (Underline)
UBlue='\e[4;34m'        # Blue  (Underline)
UPurple='\e[4;35m'      # Purple  (Underline)
UCyan='\e[4;36m'        # Cyan  (Underline)
UWhite='\e[4;37m'       # White  (Underline)

# Background
On_Black='\e[40m'       # Black (BackGropund)
On_Red='\e[41m'         # Red (BackGropund)
On_Green='\e[42m'       # Green (BackGropund)
On_Yellow='\e[43m'      # Yellow (BackGropund)
On_Blue='\e[44m'        # Blue (BackGropund)
On_Purple='\e[45m'      # Purple (BackGropund)
On_Cyan='\e[46m'        # Cyan (BackGropund)
On_White='\e[47m'       # White (BackGropund)

# High Intensity
IBlack='\e[0;90m'       # Black (Intensitive)
IRed='\e[0;91m'         # Red (Intensitive)
IGreen='\e[0;92m'       # Green (Intensitive)
IYellow='\e[0;93m'      # Yellow (Intensitive)
IBlue='\e[0;94m'        # Blue (Intensitive)
IPurple='\e[0;95m'      # Purple (Intensitive)
ICyan='\e[0;96m'        # Cyan (Intensitive)
IWhite='\e[0;97m'       # White (Intensitive)

# Bold High Intensity
BIBlack='\e[1;90m'      # Black
BIRed='\e[1;91m'        # Red
BIGreen='\e[1;92m'      # Green
BIYellow='\e[1;93m'     # Yellow
BIBlue='\e[1;94m'       # Blue
BIPurple='\e[1;95m'     # Purple
BICyan='\e[1;96m'       # Cyan
BIWhite='\e[1;97m'      # White

# High Intensity backgrounds
On_IBlack='\e[0;100m'   # Black
On_IRed='\e[0;101m'     # Red
On_IGreen='\e[0;102m'   # Green
On_IYellow='\e[0;103m'  # Yellow
On_IBlue='\e[0;104m'    # Blue
On_IPurple='\e[0;105m'  # Purple
On_ICyan='\e[0;106m'    # Cyan
On_IWhite='\e[0;107m'   # White




# --------------------------------------------------------------------
# ------🏷️ colores que afectas a las lineas siguientes --------------
# -------------------------------------------------------------------


# ✔️:::::::::  ejemplo Uso:
#        echo "$BGreen"
#        echo "uno"
#        echo "dad"
#        echo "$Yellow"
#        echo "uno"



# Regular Colors
Black_full=`echo -en "\e[0;30m"`        # Black
Red_full=`echo -en "\e[0;31m"`          # Red
Green_full=`echo -en "\e[0;32m"`        # Green
Yellow_full=`echo -en "\e[0;33m"`       # Yellow
Blue_full=`echo -en "\e[0;34m"`         # Blue
Purple_full=`echo -en "\e[0;35m"`       # Purple
Cyan_full=`echo -en "\e[0;36m"`         # Cyan
White_full=`echo -en "\e[0;37m"`        # White

# Bold
BBlack_full=`echo -en "\e[1;30m"`       # Black
BRed_full=`echo -en "\e[1;31m"`         # Red
BGreen_full=`echo -en "\e[1;32m"`       # Green
BYellow_full=`echo -en "\e[1;33m"`      # Yellow
BBlue_full=`echo -en "\e[1;34m"`        # Blue
BPurple_full=`echo -en "\e[1;35m"`      # Purple
BCyan_full=`echo -en "\e[1;36m"`        # Cyan
BWhite_full=`echo -en "\e[1;37m"`       # White


# Underline
UBlack_full=`echo -en "\e[4;30m"`       # Black
URed_full=`echo -en "\e[4;31m"`         # Red
UGreen_full=`echo -en "\e[4;32m"`       # Green
UYellow_full=`echo -en "\e[4;33m"`      # Yellow
UBlue_full=`echo -en "\e[4;34m"`        # Blue
UPurple_full=`echo -en "\e[4;35m"`      # Purple
UCyan_full=`echo -en "\e[4;36m"`        # Cyan
UWhite_full=`echo -en "\e[4;37m"`       # White

# Background
On_Black_full=`echo -en "\e[40m"`       # Black
On_Red_full=`echo -en "\e[41m"`         # Red
On_Green_full=`echo -en "\e[42m"`       # Green
On_Yellow_full=`echo -en "\e[43m"`      # Yellow
On_Blue_full=`echo -en "\e[44m"`        # Blue
On_Purple_full=`echo -en "\e[45m"`      # Purple
On_Cyan_full=`echo -en "\e[46m"`        # Cyan
On_White_full=`echo -en "\e[47m"`       # White

# High Intensity
IBlack_full=`echo -en "\e[0;90m"`       # Black
IRed_full=`echo -en "\e[0;91m"`         # Red
IGreen_full=`echo -en "\e[0;92m"`       # Green
IYellow_full=`echo -en "\e[0;93m"`      # Yellow
IBlue_full=`echo -en "\e[0;94m"`        # Blue
IPurple_full=`echo -en "\e[0;95m"`      # Purple
ICyan_full=`echo -en "\e[0;96m"`        # Cyan
IWhite_full=`echo -en "\e[0;97m"`       # White

# Bold High Intensity
BIBlack_full=`echo -en "\e[1;90m"`      # Black
BIRed_full=`echo -en "\e[1;91m"`        # Red
BIGreen_full=`echo -en "\e[1;92m"`      # Green
BIYellow_full=`echo -en "\e[1;93m"`     # Yellow
BIBlue_full=`echo -en "\e[1;94m"`       # Blue
BIPurple_full=`echo -en "\e[1;95m"`     # Purple
BICyan_full=`echo -en "\e[1;96m"`       # Cyan
BIWhite_full=`echo -en "\e[1;97m"`      # White

# High Intensity backgrounds
On_IBlack_full=`echo -en "\e[0;100m"`   # Black
On_IRed_full=`echo -en "\e[0;101m"`     # Red
On_IGreen_full=`echo -en "\e[0;102m"`   # Green
On_IYellow_full=`echo -en "\e[0;103m"`  # Yellow
On_IBlue_full=`echo -en "\e[0;104m"`    # Blue
On_IPurple_full=`echo -en "\e[0;105m"`  # Purple
On_ICyan_full=`echo -en "\e[0;106m"`    # Cyan
On_IWhite_full=`echo -en "\e[0;107m"`   # White



# ------------------------------- ver colores en vivo
#echo " ----------- # Regular Colors ------------"
#echo -en "${Black} color Black ${Color_Off} \n"
#echo -en "${Red} color Red ${Color_Off} \n"
#echo -en "${Green} color Green ${Color_Off} \n"
#echo -en "${Yellow} color Yellow ${Color_Off} \n"
#echo -en "${Blue} color Blue ${Color_Off} \n"
#echo -en "${Purple} color Purple ${Color_Off} \n"
#echo -en "${Cyan} color Cyan ${Color_Off} \n"
#echo -en "${White} color White ${Color_Off} \n"
#echo -en "${Gray} color Gray ${Color_Off} \n"
#
#
#echo " ----------- # Bold ------------"
#
## Bold
#echo -en "${BBlack} color BBlack ${Color_Off} \n"
#echo -en "${BRed} color BRed ${Color_Off} \n"
#echo -en "${BGreen} color BGreen ${Color_Off} \n"
#echo -en "${BYellow} color BYellow ${Color_Off} \n"
#echo -en "${BBlue} color BBlue ${Color_Off} \n"
#echo -en "${BPurple} color BPurple ${Color_Off} \n"
#echo -en "${BCyan} color BCyan ${Color_Off} \n"
#echo -en "${BWhite} color BWhite ${Color_Off} \n"
#echo -en "${BGray} color BGray ${Color_Off} \n"
#
#
#
#echo " ----------- # Underline ------------"
#
#echo -en "${UBlack}color UBlack${Color_Off} \n"
#echo -en "${URed}color URed${Color_Off} \n"
#echo -en "${UGreen}color UGreen${Color_Off} \n"
#echo -en "${UYellow}color UYellow${Color_Off} \n"
#echo -en "${UBlue}color UBlue${Color_Off} \n"
#echo -en "${UPurple}color UPurple${Color_Off} \n"
#echo -en "${UCyan}color UCyan${Color_Off} \n"
#echo -en "${UWhite}color UWhite${Color_Off} \n"
#
#echo " ----------- # Background ------------"
#
#echo -en "${On_Black}color On_Black ${Color_Off} \n"
#echo -en "${On_Red}color On_Red ${Color_Off} \n"
#echo -en "${On_Green}color On_Green ${Color_Off} \n"
#echo -en "${On_Yellow}color On_Yellow ${Color_Off} \n"
#echo -en "${On_Blue}color On_Blue ${Color_Off} \n"
#echo -en "${On_Purple}color On_Purple ${Color_Off} \n"
#echo -en "${On_Cyan}color On_Cyan ${Color_Off} \n"
#echo -en "${On_White}color On_White ${Color_Off} \n"
#
#echo " ----------- # High Intensity ------------"
#echo -en "${IBlack} intensidad IBlack ${Color_Off} \n"
#echo -en "${IRed} intensidad IRed ${Color_Off} \n"
#echo -en "${IGreen} intensidad IGreen ${Color_Off} \n"
#echo -en "${IYellow} intensidad IYellow ${Color_Off} \n"
#echo -en "${IBlue} intensidad IBlue ${Color_Off} \n"
#echo -en "${IPurple} intensidad IPurple ${Color_Off} \n"
#echo -en "${ICyan} intensidad ICyan ${Color_Off} \n"
#echo -en "${IWhite} intensidad IWhite ${Color_Off} \n"
#
#echo " ----------- # Bold High Intensity ------------"
#
#echo -en "${BIBlack} bold intensidad BIBlack ${Color_Off} \n"
#echo -en "${BIRed} bold intensidad BIRed ${Color_Off} \n"
#echo -en "${BIGreen} bold intensidad BIGreen ${Color_Off} \n"
#echo -en "${BIYellow} bold intensidad BIYellow ${Color_Off} \n"
#echo -en "${BIBlue} bold intensidad BIBlue ${Color_Off} \n"
#echo -en "${BIPurple} bold intensidad BIPurple ${Color_Off} \n"
#echo -en "${BICyan} bold intensidad BICyan ${Color_Off} \n"
#echo -en "${BIWhite} bold intensidad BIWhite ${Color_Off} \n"
#
#echo " ----------- # High Intensity backgrounds------------"
#echo -en "${On_IBlack} bacckground intensidad On_IBlack ${Color_Off} \n"
#echo -en "${On_IRed} bacckground intensidad On_IRed ${Color_Off} \n"
#echo -en "${On_IGreen} bacckground intensidad On_IGreen ${Color_Off} \n"
#echo -en "${On_IYellow} bacckground intensidad On_IYellow ${Color_Off} \n"
#echo -en "${On_IBlue} bacckground intensidad On_IBlue ${Color_Off} \n"
#echo -en "${On_IPurple} bacckground intensidad On_IPurple ${Color_Off} \n"
#echo -en "${On_ICyan} bacckground intensidad On_ICyan ${Color_Off} \n"



# -----------------------------------------------
# ------------------------- USO
#echo -en " -- ---- * command ${Red}:  ver menu ${Color_Off} favoritos \n"
#printf  " -- ---- * command ${Red}: %s ver menu ${Color_Off} favoritos \n" "param"



# -----------------------------------------------
# ---llamar a colores desde otro file
#if [ -f ./colors.sh ]; then
#  ./colors.sh
#fi
#




#read -p "Press [Enter] key to start backup..."
