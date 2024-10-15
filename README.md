#ePayco plugin para OpenCart v2.2.x o superior

**Si usted tiene alguna pregunta o problema, no dude en ponerse en contacto con nuestro soporte técnico: desarrollo@payco.co.**

## Tabla de contenido

* [Requisitos](#requisitos)
* [Instalación](#instalación)
* [Configuración](#configuración)
* [Pasos](#pasos)
* [Versiones](#versiones)

## Requisitos

* Tener una cuenta activa en [ePayco](https://pagaycobra.com).
* Tener instalado OpenCart v1.5.x o superior.
* Acceso a las carpetas donde se encuetra instalado OpenCart.

## Instalación

1. [Descarga el plugin.](https://github.com/epayco/Plugin_ePayco_OpenCart/releases)
2. Debes descomprimir el plugin y navegar hasta la carpeta que dice **opencart**, selecciona las carpetas que se encuentran en esa ubicación y luego debes comprimirlas en un archivo llamado **epayco.ocmod.zip**.
3. Ingresa al panel de administración de tu tienda de opencart, ingresas a **Extensions**  y en **installer** subes el archivo **epayco.ocmod.zip** y luego lo instalas desde el boton verde.

## Configuración

1. Para configurar el Plugin de ePayco, ingrese al administrador de opencart, ubique la sección **Extensions** en el menú principal, despliegue las opciones y haga clic sobre la opción **Payment**.
2. En la sección Payment encontrara una tabla con los métodos de pagos disponibles en el open cart entre ellos ePayco, posiciónese sobre el y ubíquese en la columna Action de la tabla, haga clic en link **Install**, ahora el plugin se encuentra habilitado.
3. Ahora puede ver dos nuevos link presione el link Edit, para configurar el plugin.
4. Configure los siguientes campos:

	**Order Status**: Estado final de la orden. 
	**Geo Zone** Cónfiguración de la zona geografica 
	* **Status**: Si (activara el medio de pago)
	**P_CUST_ID_CLIENTE**: ID del comercio otorgada desde el dashboard de ePayco. 
	**PUBLIC_KEY**: Llave publica otorgada desde el dashboard de ePayco.
	**PUBLIC_KEY**: Llave privada otorgada desde el dashboard de ePayco.
	**P_KEY**: Llave otorgada desde el dashboard de ePayco.
	**One Page Checkout**: Si (se retornara el boton de checkout en la misma tienda ) No (sera redirecionado a la pagina del checkout externo.)
	**Prueba**: Si (para realizar pruebas) o No (pasar a producción).
	**Sort Order** Opcional por si quieres clasificar el metodo de pago en algun orden, si es que ya tenias otros metodos de pago ya instalados 

Luego de configurar los campos presione el botón **Save**. Y con esto quedara instalado, configurado y activado el método de pago para los clientes.


## Pasos

<img src="ImgTutorialOpenCart/tuto-1.jpg" width="400px"/>
<img src="ImgTutorialOpenCart/tuto-2.jpg" width="400px"/>
<img src="ImgTutorialOpenCart/tuto-3.jpg" width="400px"/>
<img src="ImgTutorialOpenCart/tuto-4.jpg" width="400px"/>

## Versiones
* [ePayco plugin OpenCart v4.0.1](https://github.com/epayco/Plugin_ePayco_OpenCart/releases/tag/4.0.2).
* [ePayco plugin OpenCart v4.0.1](https://github.com/epayco/Plugin_ePayco_OpenCart/releases/tag/4.0.1).
* [ePayco plugin OpenCart v4.0](https://github.com/epayco/Plugin_ePayco_OpenCart/releases/tag/4.0.0.0).
* [ePayco plugin OpenCart v3.0](https://github.com/epayco/Plugin_ePayco_OpenCart/releases/tag/3.0).
* [ePayco plugin OpenCart v2.3.0.3](https://github.com/epayco/Plugin_ePayco_OpenCart/releases/tag/2.3.0.3).
* [ePayco plugin OpenCart v2.2.x](https://github.com/epayco/Plugin_ePayco_OpenCart/releases/tag/2.2.x).
* [ePayco plugin OpenCart v2.1.x](https://github.com/epayco/Plugin_ePayco_OpenCart/releases/tag/2.1.x).
* [ePayco plugin OpenCart v1.5.x](https://github.com/epayco/Plugin_ePayco_OpenCart/releases/tag/1.5.x).

