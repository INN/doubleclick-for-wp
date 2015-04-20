# ![Screenshot](img/dfw.png) DoubleClick for WordPress 

Serve DoubleClick ads natively in WordPress. Built to make serving responsive ads easy.

Built by the [Institute for Nonprofit News](http://inn.org/), DoubleClick for WordPress works with [Project Largo](http://largoproject.org/) or any standalone WordPress blog.

* * * 

## 1. What is this thing?

This WordPress plugin gives site administrators an easy way to serve DFP inventory on their WordPress site.

Implementing is simple. Configure your network code and input your identifiers. No need to copy and paste ad codes or header tags — the plugin generates all of this on its own.

* * * 

## 2. How do I use it?

For the most basic integration, only two steps are necessary.

#### __2.1. Define Settings__ — 

Under _Settings > DoubleClick for WordPress_ update the field with your network code from DFP, and a place to define breakpoints to serve ads to.

![Screenshot](img/network-code.png)

#### __2.2 Install a Widgets__ — 

Add a new widget to an existing sidebar.

![Screenshot](img/widget.png)

 * The __identifier__ field is passed back to DFP with the ad server request. If an ad unit with the same identifier is defined in your network's inventory, line items that target that ad unit will be shown in the spot. If the identifier is not defined, run of network inventory will still be loaded.

 * The __width__ and __height__ fields define what size creative to load in pixels. Flex ad units are currently not yet supported.

* * * 

## 3. Is that it?

Nope! Next [configure targeting](targeting/) and advanced users may look at the [developer api](developer-api/).