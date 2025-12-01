#!/bin/bash

# Este script permite seleccionar y aplicar una configuración IP a Netplan.
# Adaptado para la interfaz enp1s0 de Ubuntu Server 24.

NETPLAN_DIR="/etc/netplan/"
NETPLAN_FILE=$(ls -1 "${NETPLAN_DIR}" | grep -E '.*.yaml$' | head -n 1)
FULL_NETPLAN_PATH="${NETPLAN_DIR}${NETPLAN_FILE}"
BACKUP_FILE="${FULL_NETPLAN_PATH}.bak_$(date +%Y%m%d%H%M%S)"
INTERFACE="enp1s0"

echo "Iniciando el cambio de IP para la interfaz ${INTERFACE}..."

# 1. Verificar que el script se ejecute con root
if [ "$(id -u)" -ne 0 ]; then
  echo "Este script debe ejecutarse con sudo. Ejemplo: sudo ./cambiar_ip.sh"
  exit 1
fi

# 2. Verificar que se encontró el archivo de Netplan
if [ -z "$NETPLAN_FILE" ]; then
    echo "Error: No se encontró ningún archivo YAML en ${NETPLAN_DIR}. Abortando."
    exit 1
fi
echo "Archivo de Netplan encontrado: ${FULL_NETPLAN_PATH}"

# 3. Hacer una copia de seguridad del archivo Netplan actual
echo "Haciendo copia de seguridad de ${FULL_NETPLAN_PATH} a ${BACKUP_FILE}..."
cp "${FULL_NETPLAN_PATH}" "${BACKUP_FILE}"
if [ $? -ne 0 ]; then
  echo "Error: No se pudo hacer la copia de seguridad. Abortando."
  exit 1
fi
echo "Copia de seguridad creada en ${BACKUP_FILE}."

# --- Variables para las configuraciones predefinidas ---
# Configuración 1: Estática 192.168.1.50
IP_OPT1="192.168.1.50/24"
GW_OPT1="192.168.1.1"
DNS_OPT1_1="1.1.1.1"
DNS_OPT1_2="8.8.8.8"

# Configuración 2: Estática 192.168.1.60
IP_OPT2="192.168.1.60/24"
GW_OPT2="192.168.1.1"
DNS_OPT2_1="1.1.1.1"
DNS_OPT2_2="8.8.8.8"

# Configuración 3: Volver a DHCP
DHCP_OPT="dhcp"

# --- Menú de Selección ---
CHOICE=""
while [[ ! "$CHOICE" =~ ^[1-3]$ ]]; do
  echo -e "\n--- Seleccione la configuración IP a aplicar ---"
  echo "1) IP Estática: ${IP_OPT1} (Puerta de enlace: ${GW_OPT1})"
  echo "2) IP Estática: ${IP_OPT2} (Puerta de enlace: ${GW_OPT2})"
  echo "3) IP Dinámica: (Volver a DHCP)"
  echo -n "Ingrese su elección (1, 2 o 3): "
  read CHOICE

  case $CHOICE in
    1)
      NEW_IP="$IP_OPT1"
      GATEWAY="$GW_OPT1"
      DNS1="$DNS_OPT1_1"
      DNS2="$DNS_OPT1_2"
      CONFIG_TYPE="estatica"
      echo "Ha seleccionado: IP Estática ${NEW_IP}"
      ;;
    2)
      NEW_IP="$IP_OPT2"
      GATEWAY="$GW_OPT2"
      DNS1="$DNS_OPT2_1"
      DNS2="$DNS_OPT2_2"
      CONFIG_TYPE="estatica"
      echo "Ha seleccionado: IP Estática ${NEW_IP}"
      ;;
    3)
      CONFIG_TYPE="dhcp"
      echo "Ha seleccionado: Volver a IP Dinámica (DHCP)"
      ;;
    *)
      echo "Opción no válida. Por favor, ingrese 1, 2 o 3."
      ;;
  esac
done

# 4. Generar el nuevo contenido del archivo Netplan
echo -e "\nGenerando nueva configuración para ${INTERFACE}..."
if [ "$CONFIG_TYPE" == "estatica" ]; then
    NEW_CONFIG=$(cat <<EOF
network:
  version: 2
  ethernets:
    ${INTERFACE}:
      dhcp4: no
      addresses:
        - ${NEW_IP}
      routes:
        - to: default
          via: ${GATEWAY}
      nameservers:
        addresses:
          - ${DNS1}
          - ${DNS2}
EOF
)
else # DHCP
    NEW_CONFIG=$(cat <<EOF
network:
  version: 2
  ethernets:
    ${INTERFACE}:
      dhcp4: yes
EOF
)
fi

# 5. Escribir la nueva configuración en el archivo Netplan
echo "Escribiendo nueva configuración en ${FULL_NETPLAN_PATH}..."
echo "${NEW_CONFIG}" | sudo tee "${FULL_NETPLAN_PATH}" > /dev/null
if [ $? -ne 0 ]; then
  echo "Error: No se pudo escribir la nueva configuración. Abortando."
  exit 1
fi
echo "Nueva configuración escrita."

# 6. Aplicar los cambios de Netplan
echo -e "\nAplicando cambios de Netplan..."
netplan apply
if [ $? -ne 0 ]; then
  echo "Error: Los cambios de Netplan no pudieron aplicarse. Revise la configuración."
  echo "Revertiendo a la copia de seguridad..."
  sudo cp "${BACKUP_FILE}" "${FULL_NETPLAN_PATH}"
  netplan apply # Intenta aplicar la versión original
  echo "Revertido a la configuración original. Por favor, reinicie si sigue sin conexión."
  exit 1
fi

echo "Cambios de Netplan aplicados correctamente."
echo "Verificando nueva configuración para ${INTERFACE}..."
ip -4 a show "${INTERFACE}" | grep "inet " | awk '{print $2}'

# 7. Realizar comprobación de conectividad
echo -e "\nRealizando comprobación de conectividad (ping a 8.8.8.8)..."
ping -c 4 8.8.8.8 # Ping 4 veces al servidor DNS de Google

if [ $? -eq 0 ]; then
  echo -e "\n¡Comprobación de conectividad exitosa! 🎉"
else
  echo -e "\n¡ADVERTENCIA! Falló la comprobación de conectividad. ⚠️"
  echo "Verifique que la puerta de enlace y los servidores DNS sean correctos."
fi

echo -e "\nFin del script."

exit 0
