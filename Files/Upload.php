<?php

// MvcMyLibrary is a PHP framework for creating and managing dynamic content
//
// Copyright (C) 2009 - 2025  Antonio Gallo (info@laboratoriolibero.com)
// See COPYRIGHT.txt and LICENSE.txt.
//
// This file is part of MvcMyLibrary
//
// MvcMyLibrary is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// MvcMyLibrary is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with MvcMyLibrary.  If not, see <http://www.gnu.org/licenses/>.

if (!defined('EG')) die('Direct access not allowed!');

//class to manage upload files
class Files_Upload
{
	const DS = DIRECTORY_SEPARATOR;

	private $base = null; //root directory
	private $directory = null; //current directory. Path relative to the base directory (Files_Upload::base)
	private $parentDir = null; //parent folder
	private $subDir = array(); //subdirectories of the current directory
	private $relSubDir = array(); //subfolders of $this->directory. The path starts from the $base folder
	private $files = array(); //files inside the current directory
	private $relFiles = array(); //files inside $this->directory. The path starts from the $base directory
	private $params; //class parameters
	private $pattern = null; //the pattern for the preg_match function

	protected $_resultString; //reference to the class uploadStrings containing all the result strings
	
	public $fileName = null; //the name of the last file that has been uploaded
	public $notice = null; //the result string of the operation
	
	public $ext = null; //the extension of the last file that has been uploaded
	public $mimeType = null; //the mime type of the last file that has been uploaded
	
	public static $extToMimeType = array(
		"123"	=>	"application/vnd.lotus-1-2-3",
		"3dml"	=>	"text/vnd.in3d.3dml",
		"3g2"	=>	"video/3gpp2,audio/3gpp2,audio/mp4",
		"3ga"	=>	"audio/mp4",
		"3gp"	=>	"video/3gpp,audio/mp4",
		"3gp2"	=>	"audio/mp4",
		"3gpa"	=>	"audio/mp4",
		"3gpp"	=>	"audio/mp4",
		"3gpp2"	=>	"audio/mp4",
		"7z"	=>	"application/x-7z-compressed",
		"a"	=>	"application/octet-stream",
		"aab"	=>	"application/x-authorware-bin",
		"aac"	=>	"audio/aac",
		"aacp"	=>	"audio/aacp",
		"aam"	=>	"application/x-authorware-map",
		"aas"	=>	"application/x-authorware-seg",
		"abw"	=>	"application/x-abiword",
		"abw.gz"	=>	"application/x-abiword",
		"acc"	=>	"application/vnd.americandynamics.acc",
		"ace"	=>	"application/x-ace-compressed",
		"acu"	=>	"application/vnd.acucobol",
		"acutc"	=>	"application/vnd.acucorp",
		"adp"	=>	"audio/adpcm",
		"aep"	=>	"application/vnd.audiograph",
		"aff"	=>	"audio/aiff",
		"afm"	=>	"application/x-font-type1",
		"afp"	=>	"application/vnd.ibm.modcap",
		"ai"	=>	"application/postscript",
		"aif"	=>	"audio/aiff",
		"aiff"	=>	"audio/aiff",
		"air"	=>	"application/vnd.adobe.air-application-installer-package+zip",
		"ami"	=>	"application/vnd.amiga.ami",
		"apk"	=>	"application/vnd.android.package-archive",
		"application"	=>	"application/x-ms-application",
		"apr"	=>	"application/vnd.lotus-approach",
		"arw"	=>	"image/x-sony-arw",
		"asc"	=>	"application/pgp-signature",
		"asf"	=>	"video/x-ms-asf",
		"asm"	=>	"text/x-asm",
		"aso"	=>	"application/vnd.accpac.simply.aso",
		"asx"	=>	"video/x-ms-asf",
		"atc"	=>	"application/vnd.acucorp",
		"atom"	=>	"application/atom+xml",
		"atomcat"	=>	"application/atomcat+xml",
		"atomsvc"	=>	"application/atomsvc+xml",
		"atx"	=>	"application/vnd.antix.game-component",
		"au"	=>	"audio/basic",
		"avi"	=>	"video/x-msvideo",
		"avif"	=>	"image/avif",
		"avifs"	=>	"image/avif-sequence",
		"aw"	=>	"application/applixware",
		"azf"	=>	"application/vnd.airzip.filesecure.azf",
		"azs"	=>	"application/vnd.airzip.filesecure.azs",
		"azw"	=>	"application/vnd.amazon.ebook",
		"bat"	=>	"application/x-msdownload",
		"bcpio"	=>	"application/x-bcpio",
		"bdf"	=>	"application/x-font-bdf",
		"bdm"	=>	"application/vnd.syncml.dm+wbxml",
		"bh2"	=>	"application/vnd.fujitsu.oasysprs",
		"bin"	=>	"application/octet-stream",
		"bmi"	=>	"application/vnd.bmi",
		"bmp"	=>	"image/bmp",
		"book"	=>	"application/vnd.framemaker",
		"box"	=>	"application/vnd.previewsystems.box",
		"boz"	=>	"application/x-bzip2",
		"bpk"	=>	"application/octet-stream",
		"btif"	=>	"image/prs.btif",
		"bz"	=>	"application/x-bzip",
		"bz2"	=>	"application/x-bzip2",
		"c"	=>	"text/x-c",
		"c4d"	=>	"application/vnd.clonk.c4group",
		"c4f"	=>	"application/vnd.clonk.c4group",
		"c4g"	=>	"application/vnd.clonk.c4group",
		"c4p"	=>	"application/vnd.clonk.c4group",
		"c4u"	=>	"application/vnd.clonk.c4group",
		"cab"	=>	"application/vnd.ms-cab-compressed",
		"car"	=>	"application/vnd.curl.car",
		"cat"	=>	"application/vnd.ms-pki.seccat",
		"cc"	=>	"text/x-c",
		"cct"	=>	"application/x-director",
		"ccxml"	=>	"application/ccxml+xml",
		"cdbcmsg"	=>	"application/vnd.contact.cmsg",
		"cdf"	=>	"application/x-netcdf",
		"cdkey"	=>	"application/vnd.mediastation.cdkey",
		"cdr"	=>	"application/x-iso9660-image",
		"cdx"	=>	"chemical/x-cdx",
		"cdxml"	=>	"application/vnd.chemdraw+xml",
		"cdy"	=>	"application/vnd.cinderella",
		"cer"	=>	"application/pkix-cert",
		"cgm"	=>	"image/cgm",
		"chat"	=>	"application/x-chat",
		"chm"	=>	"application/vnd.ms-htmlhelp",
		"chrt"	=>	"application/vnd.kde.kchart",
		"cif"	=>	"chemical/x-cif",
		"cii"	=>	"application/vnd.anser-web-certificate-issue-initiation",
		"cil"	=>	"application/vnd.ms-artgalry",
		"cla"	=>	"application/vnd.claymore",
		"class"	=>	"application/java-vm",
		"clkk"	=>	"application/vnd.crick.clicker.keyboard",
		"clkp"	=>	"application/vnd.crick.clicker.palette",
		"clkt"	=>	"application/vnd.crick.clicker.template",
		"clkw"	=>	"application/vnd.crick.clicker.wordbank",
		"clkx"	=>	"application/vnd.crick.clicker",
		"clp"	=>	"application/x-msclip",
		"cmc"	=>	"application/vnd.cosmocaller",
		"cmdf"	=>	"chemical/x-cmdf",
		"cml"	=>	"chemical/x-cml",
		"cmp"	=>	"application/vnd.yellowriver-custom-menu",
		"cmx"	=>	"image/x-cmx",
		"cod"	=>	"application/vnd.rim.cod",
		"com"	=>	"application/x-msdownload",
		"conf"	=>	"text/plain",
		"cpio"	=>	"application/x-cpio",
		"cpp"	=>	"text/x-c",
		"cpt"	=>	"application/mac-compactpro",
		"cr2"	=>	"image/x-canon-cr2",
		"crd"	=>	"application/x-mscardfile",
		"crl"	=>	"application/pkix-crl",
		"crt"	=>	"application/x-x509-ca-cert",
		"crw"	=>	"image/x-canon-crw",
		"csh"	=>	"application/x-csh",
		"csml"	=>	"chemical/x-csml",
		"csp"	=>	"application/vnd.commonspace",
		"css"	=>	"text/css",
		"cst"	=>	"application/x-director",
		"csv"	=>	"text/csv",
		"cu"	=>	"application/cu-seeme",
		"curl"	=>	"text/vnd.curl",
		"cww"	=>	"application/prs.cww",
		"cxt"	=>	"application/x-director",
		"cxx"	=>	"text/x-c",
		"daf"	=>	"application/vnd.mobius.daf",
		"dataless"	=>	"application/vnd.fdsn.seed",
		"davmount"	=>	"application/davmount+xml",
		"db"	=>	"application/vnd.sqlite3",
		"db-shm"	=>	"application/vnd.sqlite3",
		"db-wal"	=>	"application/vnd.sqlite3",
		"dcr"	=>	"application/x-director,image/x-kodak-dcr",
		"dcurl"	=>	"text/vnd.curl.dcurl",
		"dd2"	=>	"application/vnd.oma.dd2+xml",
		"ddd"	=>	"application/vnd.fujixerox.ddd",
		"deb"	=>	"application/vnd.debian.binary-package,application/x-debian-package",
		"def"	=>	"text/plain",
		"deploy"	=>	"application/octet-stream",
		"der"	=>	"application/x-x509-ca-cert",
		"dfac"	=>	"application/vnd.dreamfactory",
		"dic"	=>	"text/x-c",
		"diff"	=>	"text/plain",
		"dir"	=>	"application/x-director",
		"dis"	=>	"application/vnd.mobius.dis",
		"dist"	=>	"application/octet-stream",
		"distz"	=>	"application/octet-stream",
		"djv"	=>	"image/vnd.djvu",
		"djvu"	=>	"image/vnd.djvu",
		"dll"	=>	"application/x-msdownload",
		"dmg"	=>	"application/octet-stream",
		"dms"	=>	"application/octet-stream",
		"dna"	=>	"application/vnd.dna",
		"dng"	=>	"image/x-adobe-dng",
		"doc"	=>	"application/msword",
		"docm"	=>	"application/vnd.ms-word.document.macroenabled.12",
		"docx"	=>	"application/vnd.openxmlformats-officedocument.wordprocessingml.document",
		"dot"	=>	"application/msword",
		"dotm"	=>	"application/vnd.ms-word.template.macroenabled.12",
		"dotx"	=>	"application/vnd.openxmlformats-officedocument.wordprocessingml.template",
		"dp"	=>	"application/vnd.osgi.dp",
		"dpg"	=>	"application/vnd.dpgraph",
		"dsc"	=>	"text/prs.lines.tag",
		"dtb"	=>	"application/x-dtbook+xml",
		"dtd"	=>	"application/xml-dtd",
		"dts"	=>	"audio/vnd.dts",
		"dtshd"	=>	"audio/vnd.dts.hd",
		"dump"	=>	"application/octet-stream",
		"dvi"	=>	"application/x-dvi",
		"dwf"	=>	"model/vnd.dwf",
		"dwg"	=>	"image/vnd.dwg",
		"dxf"	=>	"image/vnd.dxf",
		"dxp"	=>	"application/vnd.spotfire.dxp",
		"dxr"	=>	"application/x-director",
		"ecelp4800"	=>	"audio/vnd.nuera.ecelp4800",
		"ecelp7470"	=>	"audio/vnd.nuera.ecelp7470",
		"ecelp9600"	=>	"audio/vnd.nuera.ecelp9600",
		"ecma"	=>	"application/ecmascript",
		"edm"	=>	"application/vnd.novadigm.edm",
		"edx"	=>	"application/vnd.novadigm.edx",
		"efif"	=>	"application/vnd.picsel",
		"ei6"	=>	"application/vnd.pg.osasli",
		"elc"	=>	"application/octet-stream",
		"eml"	=>	"message/rfc822",
		"emma"	=>	"application/emma+xml",
		"eol"	=>	"audio/vnd.digital-winds",
		"eot"	=>	"application/vnd.ms-fontobject",
		"eps"	=>	"application/postscript",
		"epub"	=>	"application/epub+zip",
		"erf"	=>	"image/x-epson-erf",
		"es3"	=>	"application/vnd.eszigno3+xml",
		"esf"	=>	"application/vnd.epson.esf",
		"et3"	=>	"application/vnd.eszigno3+xml",
		"etx"	=>	"text/x-setext",
		"exe"	=>	"application/x-msdownload",
		"ext"	=>	"application/vnd.novadigm.ext",
		"ez"	=>	"application/andrew-inset",
		"ez2"	=>	"application/vnd.ezpix-album",
		"ez3"	=>	"application/vnd.ezpix-package",
		"f"	=>	"text/x-fortran",
		"f4v"	=>	"video/x-f4v",
		"f77"	=>	"text/x-fortran",
		"f90"	=>	"text/x-fortran",
		"fbs"	=>	"image/vnd.fastbidsheet",
		"fdf"	=>	"application/vnd.fdf",
		"fe_launch"	=>	"application/vnd.denovo.fcselayout-link",
		"fg5"	=>	"application/vnd.fujitsu.oasysgp",
		"fgd"	=>	"application/x-director",
		"fh"	=>	"image/x-freehand",
		"fh4"	=>	"image/x-freehand",
		"fh5"	=>	"image/x-freehand",
		"fh7"	=>	"image/x-freehand",
		"fhc"	=>	"image/x-freehand",
		"fig"	=>	"application/x-xfig",
		"flac"	=>	"audio/flac",
		"fli"	=>	"video/x-fli",
		"flo"	=>	"application/vnd.micrografx.flo",
		"flv"	=>	"video/x-flv",
		"flw"	=>	"application/vnd.kde.kivio",
		"flx"	=>	"text/vnd.fmi.flexstor",
		"fly"	=>	"text/vnd.fly",
		"fm"	=>	"application/vnd.framemaker",
		"fnc"	=>	"application/vnd.frogans.fnc",
		"for"	=>	"text/x-fortran",
		"fpx"	=>	"image/vnd.fpx",
		"frame"	=>	"application/vnd.framemaker",
		"fsc"	=>	"application/vnd.fsc.weblaunch",
		"fst"	=>	"image/vnd.fst",
		"ftc"	=>	"application/vnd.fluxtime.clip",
		"fti"	=>	"application/vnd.anser-web-funds-transfer-initiation",
		"fvt"	=>	"video/vnd.fvt",
		"fzs"	=>	"application/vnd.fuzzysheet",
		"g3"	=>	"image/g3fax",
		"gac"	=>	"application/vnd.groove-account",
		"gbr"	=>	"application/vnd.gerber",
		"gcode"	=>	"gcode",
		"gdl"	=>	"model/vnd.gdl",
		"geo"	=>	"application/vnd.dynageo",
		"gex"	=>	"application/vnd.geometry-explorer",
		"ggb"	=>	"application/vnd.geogebra.file",
		"ggt"	=>	"application/vnd.geogebra.tool",
		"ghf"	=>	"application/vnd.groove-help",
		"gif"	=>	"image/gif",
		"gim"	=>	"application/vnd.groove-identity-message",
		"gmx"	=>	"application/vnd.gmx",
		"gnumeric"	=>	"application/x-gnumeric",
		"gph"	=>	"application/vnd.flographit",
		"gqf"	=>	"application/vnd.grafeq",
		"gqs"	=>	"application/vnd.grafeq",
		"gram"	=>	"application/srgs",
		"gre"	=>	"application/vnd.geometry-explorer",
		"grv"	=>	"application/vnd.groove-injector",
		"grxml"	=>	"application/srgs+xml",
		"gsf"	=>	"application/x-font-ghostscript",
		"gtar"	=>	"application/x-gtar",
		"gtm"	=>	"application/vnd.groove-tool-message",
		"gtw"	=>	"model/vnd.gtw",
		"gv"	=>	"text/vnd.graphviz",
		"gz"	=>	"application/x-gzip,application/gzip",
		"h"	=>	"text/x-c",
		"h261"	=>	"video/h261",
		"h263"	=>	"video/h263",
		"h264"	=>	"video/h264",
		"hbci"	=>	"application/vnd.hbci",
		"hdf"	=>	"application/x-hdf",
		"heic"	=>	"image/heic",
		"heif"	=>	"image/heic",
		"hh"	=>	"text/x-c",
		"hlp"	=>	"application/winhlp",
		"hpgl"	=>	"application/vnd.hp-hpgl",
		"hpid"	=>	"application/vnd.hp-hpid",
		"hps"	=>	"application/vnd.hp-hps",
		"hqx"	=>	"application/mac-binhex40",
		"htke"	=>	"application/vnd.kenameaapp",
		"htm"	=>	"text/html",
		"html"	=>	"text/html",
		"hvd"	=>	"application/vnd.yamaha.hv-dic",
		"hvp"	=>	"application/vnd.yamaha.hv-voice",
		"hvs"	=>	"application/vnd.yamaha.hv-script",
		"icc"	=>	"application/vnd.iccprofile",
		"ice"	=>	"x-conference/x-cooltalk",
		"icm"	=>	"application/vnd.iccprofile",
		"icns"	=>	"image/x-icns",
		"ico"	=>	"image/x-icon",
		"ics"	=>	"text/calendar",
		"ief"	=>	"image/ief",
		"ifb"	=>	"text/calendar",
		"ifm"	=>	"application/vnd.shana.informed.formdata",
		"iges"	=>	"model/iges",
		"igl"	=>	"application/vnd.igloader",
		"igs"	=>	"model/iges",
		"igx"	=>	"application/vnd.micrografx.igx",
		"iif"	=>	"application/vnd.shana.informed.interchange",
		"imp"	=>	"application/vnd.accpac.simply.imp",
		"ims"	=>	"application/vnd.ms-ims",
		"in"	=>	"text/plain",
		"inc"	=>	"text/x-pascal",
		"ipk"	=>	"application/vnd.shana.informed.package",
		"irm"	=>	"application/vnd.ibm.rights-management",
		"irp"	=>	"application/vnd.irepository.package+xml",
		"iso"	=>	"application/x-iso9660-image",
		"isoimg"	=>	"application/x-iso9660-image",
		"itp"	=>	"application/vnd.shana.informed.formtemplate",
		"ivp"	=>	"application/vnd.immervision-ivp",
		"ivu"	=>	"application/vnd.immervision-ivu",
		"jad"	=>	"text/vnd.sun.j2me.app-descriptor",
		"jam"	=>	"application/vnd.jam",
		"jar"	=>	"application/java-archive",
		"java"	=>	"text/x-java-source",
		"jfi"	=>	"image/pjpeg",
		"jfif"	=>	"image/jpeg,image/pjpeg",
		"jfif-tbnl"	=>	"image/jpeg,image/pjpeg",
		"jif"	=>	"image/jpeg,image/pjpeg",
		"jisp"	=>	"application/vnd.jisp",
		"jlt"	=>	"application/vnd.hp-jlyt",
		"jnlp"	=>	"application/x-java-jnlp-file",
		"joda"	=>	"application/vnd.joost.joda-archive",
		"jpe"	=>	"image/jpeg,image/pjpeg",
		"jpeg"	=>	"image/jpeg,image/pjpeg",
		"jpg"	=>	"image/jpeg,image/pjpeg",
		"jpgm"	=>	"video/jpm",
		"jpgv"	=>	"video/jpeg",
		"jpm"	=>	"video/jpm",
		"js"	=>	"text/javascript",
		"json"	=>	"application/json",
		"k25"	=>	"image/x-kodak-k25",
		"kar"	=>	"audio/midi",
		"karbon"	=>	"application/vnd.kde.karbon",
		"kdc"	=>	"image/x-kodak-kdc",
		"kfo"	=>	"application/vnd.kde.kformula",
		"kia"	=>	"application/vnd.kidspiration",
		"kil"	=>	"application/x-killustrator",
		"kml"	=>	"application/vnd.google-earth.kml+xml",
		"kmz"	=>	"application/vnd.google-earth.kmz",
		"kne"	=>	"application/vnd.kinar",
		"knp"	=>	"application/vnd.kinar",
		"kon"	=>	"application/vnd.kde.kontour",
		"kpr"	=>	"application/vnd.kde.kpresenter",
		"kpt"	=>	"application/vnd.kde.kpresenter",
		"kra"	=>	"application/x-krita",
		"krz"	=>	"application/x-krita",
		"ksh"	=>	"text/plain",
		"ksp"	=>	"application/vnd.kde.kspread",
		"ktr"	=>	"application/vnd.kahootz",
		"ktz"	=>	"application/vnd.kahootz",
		"kwd"	=>	"application/vnd.kde.kword",
		"kwt"	=>	"application/vnd.kde.kword",
		"latex"	=>	"application/x-latex",
		"lbd"	=>	"application/vnd.llamagraphics.life-balance.desktop",
		"lbe"	=>	"application/vnd.llamagraphics.life-balance.exchange+xml",
		"les"	=>	"application/vnd.hhe.lesson-player",
		"lha"	=>	"application/octet-stream",
		"link66"	=>	"application/vnd.route66.link66+xml",
		"list"	=>	"text/plain",
		"list3820"	=>	"application/vnd.ibm.modcap",
		"listafp"	=>	"application/vnd.ibm.modcap",
		"log"	=>	"text/plain",
		"lostxml"	=>	"application/lost+xml",
		"lrf"	=>	"application/octet-stream",
		"lrm"	=>	"application/vnd.ms-lrm",
		"ltf"	=>	"application/vnd.frogans.ltf",
		"lvp"	=>	"audio/vnd.lucent.voice",
		"lwp"	=>	"application/vnd.lotus-wordpro",
		"lzh"	=>	"application/octet-stream",
		"m13"	=>	"application/x-msmediaview",
		"m14"	=>	"application/x-msmediaview",
		"m1v"	=>	"video/mpeg",
		"m2a"	=>	"audio/mpeg",
		"m2v"	=>	"video/mpeg",
		"m3a"	=>	"audio/mpeg",
		"m3u"	=>	"audio/x-mpegurl",
		"m4a"	=>	"audio/mp4,audio/aac",
		"m4b"	=>	"audio/mp4",
		"m4p"	=>	"audio/mp4",
		"m4r"	=>	"audio/mp4",
		"m4u"	=>	"video/vnd.mpegurl",
		"m4v"	=>	"audio/mp4,video/x-m4v",
		"ma"	=>	"application/mathematica",
		"mag"	=>	"application/vnd.ecowin.chart",
		"maker"	=>	"application/vnd.framemaker",
		"man"	=>	"text/troff",
		"markdn"	=>	"text/markdown",
		"markdown"	=>	"text/markdown",
		"mathml"	=>	"application/mathml+xml,text/mathml",
		"mb"	=>	"application/mathematica",
		"mbk"	=>	"application/vnd.mobius.mbk",
		"mbox"	=>	"application/mbox",
		"mc1"	=>	"application/vnd.medcalcdata",
		"mcd"	=>	"application/vnd.mcd",
		"mcurl"	=>	"text/vnd.curl.mcurl",
		"md"	=>	"text/markdown",
		"mdb"	=>	"application/x-msaccess",
		"mdi"	=>	"image/vnd.ms-modi",
		"mdown"	=>	"text/markdown",
		"me"	=>	"text/troff",
		"mesh"	=>	"model/mesh",
		"mfm"	=>	"application/vnd.mfmp",
		"mgz"	=>	"application/vnd.proteus.magazine",
		"mht"	=>	"message/rfc822",
		"mhtml"	=>	"message/rfc822",
		"mid"	=>	"audio/midi",
		"midi"	=>	"audio/midi",
		"mif"	=>	"application/vnd.mif",
		"mime"	=>	"message/rfc822",
		"mj2"	=>	"video/mj2",
		"mjp2"	=>	"video/mj2",
		"mka"	=>	"audio/x-matroska",
		"mkv"	=>	"video/x-matroska",
		"mlp"	=>	"application/vnd.dolby.mlp",
		"mmd"	=>	"application/vnd.chipnuts.karaoke-mmd",
		"mmf"	=>	"application/vnd.smaf",
		"mml"	=>	"application/mathml+xml,text/mathml",
		"mmr"	=>	"image/vnd.fujixerox.edmics-mmr",
		"mny"	=>	"application/x-msmoney",
		"mobi"	=>	"application/x-mobipocket-ebook",
		"mov"	=>	"video/quicktime",
		"movie"	=>	"video/x-sgi-movie",
		"mp2"	=>	"audio/mpeg",
		"mp2a"	=>	"audio/mpeg",
		"mp3"	=>	"audio/mpeg",
		"mp4"	=>	"audio/mp4,video/mp4",
		"mp4s"	=>	"application/mp4",
		"mp4v"	=>	"audio/mp4,video/mp4",
		"mpa"	=>	"video/mpeg",
		"mpc"	=>	"application/vnd.mophun.certificate",
		"mpe"	=>	"video/mpeg",
		"mpeg"	=>	"video/mpeg",
		"mpg"	=>	"video/mpeg",
		"mpg4"	=>	"video/mp4",
		"mpga"	=>	"audio/mpeg",
		"mpkg"	=>	"application/vnd.apple.installer+xml",
		"mpm"	=>	"application/vnd.blueice.multipass",
		"mpn"	=>	"application/vnd.mophun.application",
		"mpp"	=>	"application/vnd.ms-project",
		"mpt"	=>	"application/vnd.ms-project",
		"mpy"	=>	"application/vnd.ibm.minipay",
		"mqy"	=>	"application/vnd.mobius.mqy",
		"mrc"	=>	"application/marc",
		"mrw"	=>	"image/x-minolta-mrw",
		"ms"	=>	"text/troff",
		"mscml"	=>	"application/mediaservercontrol+xml",
		"mseed"	=>	"application/vnd.fdsn.mseed",
		"mseq"	=>	"application/vnd.mseq",
		"msf"	=>	"application/vnd.epson.msf",
		"msh"	=>	"model/mesh",
		"msi"	=>	"application/x-msdownload",
		"msl"	=>	"application/vnd.mobius.msl",
		"msty"	=>	"application/vnd.muvee.style",
		"mts"	=>	"model/vnd.mts",
		"mus"	=>	"application/vnd.musician",
		"musicxml"	=>	"application/vnd.recordare.musicxml+xml",
		"mvb"	=>	"application/x-msmediaview",
		"mwf"	=>	"application/vnd.mfer",
		"mxf"	=>	"application/mxf",
		"mxl"	=>	"application/vnd.recordare.musicxml",
		"mxml"	=>	"application/xv+xml",
		"mxs"	=>	"application/vnd.triscape.mxs",
		"mxu"	=>	"video/vnd.mpegurl",
		"n-gage"	=>	"application/vnd.nokia.n-gage.symbian.install",
		"nb"	=>	"application/mathematica",
		"nc"	=>	"application/x-netcdf",
		"ncx"	=>	"application/x-dtbncx+xml",
		"nef"	=>	"image/x-nikon-nef",
		"ngdat"	=>	"application/vnd.nokia.n-gage.data",
		"nlu"	=>	"application/vnd.neurolanguage.nlu",
		"nml"	=>	"application/vnd.enliven",
		"nnd"	=>	"application/vnd.noblenet-directory",
		"nns"	=>	"application/vnd.noblenet-sealer",
		"nnw"	=>	"application/vnd.noblenet-web",
		"npx"	=>	"image/vnd.net-fpx",
		"nsf"	=>	"application/vnd.lotus-notes",
		"nws"	=>	"message/rfc822",
		"o"	=>	"application/octet-stream",
		"oa2"	=>	"application/vnd.fujitsu.oasys2",
		"oa3"	=>	"application/vnd.fujitsu.oasys3",
		"oas"	=>	"application/vnd.fujitsu.oasys",
		"obd"	=>	"application/x-msbinder",
		"obj"	=>	"application/octet-stream",
		"oda"	=>	"application/oda",
		"odb"	=>	"application/vnd.oasis.opendocument.database",
		"odc"	=>	"application/vnd.oasis.opendocument.chart",
		"odf"	=>	"application/vnd.oasis.opendocument.formula",
		"odft"	=>	"application/vnd.oasis.opendocument.formula-template",
		"odg"	=>	"application/vnd.oasis.opendocument.graphics",
		"odi"	=>	"application/vnd.oasis.opendocument.image",
		"odp"	=>	"application/vnd.oasis.opendocument.presentation",
		"ods"	=>	"application/vnd.oasis.opendocument.spreadsheet",
		"odt"	=>	"application/vnd.oasis.opendocument.text",
		"oga"	=>	"audio/ogg",
		"ogg"	=>	"audio/ogg",
		"ogv"	=>	"video/ogg",
		"ogx"	=>	"application/ogg",
		"onepkg"	=>	"application/onenote",
		"onetmp"	=>	"application/onenote",
		"onetoc"	=>	"application/onenote",
		"onetoc2"	=>	"application/onenote",
		"opf"	=>	"application/oebps-package+xml",
		"oprc"	=>	"application/vnd.palm",
		"opus"	=>	"audio/opus",
		"orf"	=>	"image/x-olympus-orf",
		"org"	=>	"application/vnd.lotus-organizer",
		"osf"	=>	"application/vnd.yamaha.openscoreformat",
		"osfpvg"	=>	"application/vnd.yamaha.openscoreformat.osfpvg+xml",
		"otc"	=>	"application/vnd.oasis.opendocument.chart-template",
		"otf"	=>	"application/x-font-otf,font/otf",
		"otg"	=>	"application/vnd.oasis.opendocument.graphics-template",
		"oth"	=>	"application/vnd.oasis.opendocument.text-web",
		"oti"	=>	"application/vnd.oasis.opendocument.image-template",
		"otm"	=>	"application/vnd.oasis.opendocument.text-master",
		"otp"	=>	"application/vnd.oasis.opendocument.presentation-template",
		"ots"	=>	"application/vnd.oasis.opendocument.spreadsheet-template",
		"ott"	=>	"application/vnd.oasis.opendocument.text-template",
		"oxt"	=>	"application/vnd.openofficeorg.extension",
		"p"	=>	"text/x-pascal",
		"p10"	=>	"application/pkcs10",
		"p12"	=>	"application/x-pkcs12",
		"p7b"	=>	"application/x-pkcs7-certificates",
		"p7c"	=>	"application/pkcs7-mime",
		"p7m"	=>	"application/pkcs7-mime",
		"p7r"	=>	"application/x-pkcs7-certreqresp",
		"p7s"	=>	"application/pkcs7-signature",
		"pas"	=>	"text/x-pascal",
		"pbd"	=>	"application/vnd.powerbuilder6",
		"pbm"	=>	"image/x-portable-bitmap",
		"pcf"	=>	"application/x-font-pcf",
		"pcl"	=>	"application/vnd.hp-pcl",
		"pclxl"	=>	"application/vnd.hp-pclxl",
		"pct"	=>	"image/x-pict",
		"pcurl"	=>	"application/vnd.curl.pcurl",
		"pcx"	=>	"image/x-pcx",
		"pdb"	=>	"application/vnd.palm",
		"pdf"	=>	"application/pdf",
		"pef"	=>	"image/x-pentax-pef",
		"pfa"	=>	"application/x-font-type1",
		"pfb"	=>	"application/x-font-type1",
		"pfm"	=>	"application/x-font-type1",
		"pfr"	=>	"application/font-tdpfr",
		"pfx"	=>	"application/x-pkcs12",
		"pgm"	=>	"image/x-portable-graymap",
		"pgn"	=>	"application/x-chess-pgn",
		"pgp"	=>	"application/pgp-encrypted",
		"pic"	=>	"image/x-pict",
		"pjpg"	=>	"image/jpeg,image/pjpeg",
		"pkg"	=>	"application/octet-stream",
		"pki"	=>	"application/pkixcmp",
		"pkipath"	=>	"application/pkix-pkipath",
		"pl"	=>	"text/plain,application/x-perl",
		"plb"	=>	"application/vnd.3gpp.pic-bw-large",
		"plc"	=>	"application/vnd.mobius.plc",
		"plf"	=>	"application/vnd.pocketlearn",
		"pls"	=>	"application/pls+xml",
		"pm"	=>	"application/x-perl",
		"pml"	=>	"application/vnd.ctc-posml",
		"png"	=>	"image/png",
		"pnm"	=>	"image/x-portable-anymap",
		"portpkg"	=>	"application/vnd.macports.portpkg",
		"pot"	=>	"application/vnd.ms-powerpoint",
		"potm"	=>	"application/vnd.ms-powerpoint.template.macroenabled.12",
		"potx"	=>	"application/vnd.openxmlformats-officedocument.presentationml.template",
		"pp"	=>	"text/x-pascal",
		"ppa"	=>	"application/vnd.ms-powerpoint",
		"ppam"	=>	"application/vnd.ms-powerpoint.addin.macroenabled.12",
		"ppd"	=>	"application/vnd.cups-ppd",
		"ppm"	=>	"image/x-portable-pixmap",
		"pps"	=>	"application/vnd.ms-powerpoint",
		"ppsm"	=>	"application/vnd.ms-powerpoint.slideshow.macroenabled.12",
		"ppsx"	=>	"application/vnd.openxmlformats-officedocument.presentationml.slideshow",
		"ppt"	=>	"application/vnd.ms-powerpoint",
		"pptm"	=>	"application/vnd.ms-powerpoint.presentation.macroenabled.12",
		"pptx"	=>	"application/vnd.openxmlformats-officedocument.presentationml.presentation",
		"pqa"	=>	"application/vnd.palm",
		"prc"	=>	"application/x-mobipocket-ebook",
		"pre"	=>	"application/vnd.lotus-freelance",
		"prf"	=>	"application/pics-rules",
		"prql"	=>	"application/prql",
		"ps"	=>	"application/postscript",
		"psb"	=>	"application/vnd.3gpp.pic-bw-small",
		"psd"	=>	"image/vnd.adobe.photoshop",
		"psf"	=>	"application/x-font-linux-psf",
		"ptid"	=>	"application/vnd.pvi.ptid1",
		"ptx"	=>	"image/x-pentax-pef",
		"pub"	=>	"application/x-mspublisher",
		"pvb"	=>	"application/vnd.3gpp.pic-bw-var",
		"pwn"	=>	"application/vnd.3m.post-it-notes",
		"pwz"	=>	"application/vnd.ms-powerpoint",
		"py"	=>	"text/x-python",
		"pya"	=>	"audio/vnd.ms-playready.media.pya",
		"pyc"	=>	"text/x-python",
		"pyd"	=>	"text/x-python",
		"pyo"	=>	"text/x-python",
		"pyv"	=>	"video/vnd.ms-playready.media.pyv",
		"qam"	=>	"application/vnd.epson.quickanime",
		"qbo"	=>	"application/vnd.intu.qbo",
		"qfx"	=>	"application/vnd.intu.qfx",
		"qps"	=>	"application/vnd.publishare-delta-tree",
		"qt"	=>	"video/quicktime",
		"qwd"	=>	"application/vnd.quark.quarkxpress",
		"qwt"	=>	"application/vnd.quark.quarkxpress",
		"qxb"	=>	"application/vnd.quark.quarkxpress",
		"qxd"	=>	"application/vnd.quark.quarkxpress",
		"qxl"	=>	"application/vnd.quark.quarkxpress",
		"qxt"	=>	"application/vnd.quark.quarkxpress",
		"ra"	=>	"audio/x-pn-realaudio",
		"raf"	=>	"image/x-fuji-raf",
		"ram"	=>	"audio/x-pn-realaudio",
		"rar"	=>	"application/vnd.rar,application/x-rar-compressed",
		"ras"	=>	"image/x-cmu-raster",
		"raw"	=>	"image/x-panasonic-raw",
		"rcprofile"	=>	"application/vnd.ipunplugged.rcprofile",
		"rdf"	=>	"application/rdf+xml",
		"rdz"	=>	"application/vnd.data-vision.rdz",
		"rep"	=>	"application/vnd.businessobjects",
		"res"	=>	"application/x-dtbresource+xml",
		"rgb"	=>	"image/x-rgb",
		"rif"	=>	"application/reginfo+xml",
		"rl"	=>	"application/resource-lists+xml",
		"rlc"	=>	"image/vnd.fujixerox.edmics-rlc",
		"rld"	=>	"application/resource-lists-diff+xml",
		"rm"	=>	"application/vnd.rn-realmedia",
		"rmi"	=>	"audio/midi",
		"rmp"	=>	"audio/x-pn-realaudio-plugin",
		"rms"	=>	"application/vnd.jcp.javame.midlet-rms",
		"rnc"	=>	"application/relax-ng-compact-syntax",
		"roff"	=>	"text/troff",
		"rpa"	=>	"application/x-redhat-package-manager",
		"rpm"	=>	"application/x-rpm",
		"rpss"	=>	"application/vnd.nokia.radio-presets",
		"rpst"	=>	"application/vnd.nokia.radio-preset",
		"rq"	=>	"application/sparql-query",
		"rs"	=>	"application/rls-services+xml",
		"rsd"	=>	"application/rsd+xml",
		"rss"	=>	"application/rss+xml",
		"rtf"	=>	"application/rtf",
		"rtx"	=>	"text/richtext",
		"rw2"	=>	"image/x-panasonic-raw",
		"rwl"	=>	"image/x-panasonic-raw",
		"s"	=>	"text/x-asm",
		"saf"	=>	"application/vnd.yamaha.smaf-audio",
		"sbml"	=>	"application/sbml+xml",
		"sc"	=>	"application/vnd.ibm.secure-container",
		"scd"	=>	"application/x-msschedule",
		"scm"	=>	"application/vnd.lotus-screencam",
		"scq"	=>	"application/scvp-cv-request",
		"scs"	=>	"application/scvp-cv-response",
		"scurl"	=>	"text/vnd.curl.scurl",
		"sda"	=>	"application/vnd.stardivision.draw",
		"sdc"	=>	"application/vnd.stardivision.calc",
		"sdd"	=>	"application/vnd.stardivision.impress",
		"sdkd"	=>	"application/vnd.solent.sdkm+xml",
		"sdkm"	=>	"application/vnd.solent.sdkm+xml",
		"sdp"	=>	"application/sdp",
		"sdw"	=>	"application/vnd.stardivision.writer",
		"see"	=>	"application/vnd.seemail",
		"seed"	=>	"application/vnd.fdsn.seed",
		"sema"	=>	"application/vnd.sema",
		"semd"	=>	"application/vnd.semd",
		"semf"	=>	"application/vnd.semf",
		"ser"	=>	"application/java-serialized-object",
		"setpay"	=>	"application/set-payment-initiation",
		"setreg"	=>	"application/set-registration-initiation",
		"sfd-hdstx"	=>	"application/vnd.hydrostatix.sof-data",
		"sfs"	=>	"application/vnd.spotfire.sfs",
		"sgl"	=>	"application/vnd.stardivision.writer-global",
		"sgm"	=>	"text/sgml",
		"sgml"	=>	"text/sgml",
		"sh"	=>	"application/x-shellscript,application/x-sh",
		"shar"	=>	"application/x-shar",
		"shf"	=>	"application/shf+xml",
		"si"	=>	"text/vnd.wap.si",
		"sic"	=>	"application/vnd.wap.sic",
		"sig"	=>	"application/pgp-signature",
		"silo"	=>	"model/mesh",
		"sis"	=>	"application/vnd.symbian.install",
		"sisx"	=>	"application/vnd.symbian.install",
		"sit"	=>	"application/x-stuffit",
		"sitx"	=>	"application/x-stuffitx",
		"skd"	=>	"application/vnd.koan",
		"skm"	=>	"application/vnd.koan",
		"skp"	=>	"application/vnd.koan",
		"skt"	=>	"application/vnd.koan",
		"sl"	=>	"text/vnd.wap.sl",
		"slc"	=>	"application/vnd.wap.slc",
		"sldm"	=>	"application/vnd.ms-powerpoint.slide.macroenabled.12",
		"sldx"	=>	"application/vnd.openxmlformats-officedocument.presentationml.slide",
		"slt"	=>	"application/vnd.epson.salt",
		"smf"	=>	"application/vnd.stardivision.math",
		"smi"	=>	"application/smil+xml",
		"smil"	=>	"application/smil+xml",
		"snd"	=>	"audio/basic",
		"snf"	=>	"application/x-font-snf",
		"so"	=>	"application/octet-stream",
		"spc"	=>	"application/x-pkcs7-certificates",
		"spf"	=>	"application/vnd.yamaha.smaf-phrase",
		"spl"	=>	"application/x-futuresplash",
		"spot"	=>	"text/vnd.in3d.spot",
		"spp"	=>	"application/scvp-vp-response",
		"spq"	=>	"application/scvp-vp-request",
		"spx"	=>	"audio/ogg",
		"sqlite"	=>	"application/vnd.sqlite3",
		"sqlite-shm"	=>	"application/vnd.sqlite3",
		"sqlite-wal"	=>	"application/vnd.sqlite3",
		"sqlite3"	=>	"application/vnd.sqlite3",
		"sr2"	=>	"image/x-sony-sr2",
		"src"	=>	"application/x-wais-source",
		"srf"	=>	"image/x-sony-srf",
		"srx"	=>	"application/sparql-results+xml",
		"sse"	=>	"application/vnd.kodak-descriptor",
		"ssf"	=>	"application/vnd.epson.ssf",
		"ssml"	=>	"application/ssml+xml",
		"stc"	=>	"application/vnd.sun.xml.calc.template",
		"std"	=>	"application/vnd.sun.xml.draw.template",
		"stf"	=>	"application/vnd.wt.stf",
		"sti"	=>	"application/vnd.sun.xml.impress.template",
		"stk"	=>	"application/hyperstudio",
		"stl"	=>	"application/vnd.ms-pki.stl",
		"str"	=>	"application/vnd.pg.format",
		"stw"	=>	"application/vnd.sun.xml.writer.template",
		"sus"	=>	"application/vnd.sus-calendar",
		"susp"	=>	"application/vnd.sus-calendar",
		"sv4cpio"	=>	"application/x-sv4cpio",
		"sv4crc"	=>	"application/x-sv4crc",
		"svd"	=>	"application/vnd.svd",
		"svg"	=>	"image/svg+xml",
		"svgz"	=>	"image/svg+xml",
		"swa"	=>	"application/x-director",
		"swf"	=>	"application/x-shockwave-flash",
		"swi"	=>	"application/vnd.arastra.swi",
		"sxc"	=>	"application/vnd.sun.xml.calc",
		"sxd"	=>	"application/vnd.sun.xml.draw",
		"sxg"	=>	"application/vnd.sun.xml.writer.global",
		"sxi"	=>	"application/vnd.sun.xml.impress",
		"sxm"	=>	"application/vnd.sun.xml.math",
		"sxw"	=>	"application/vnd.sun.xml.writer",
		"t"	=>	"text/troff",
		"tao"	=>	"application/vnd.tao.intent-module-archive",
		"tar"	=>	"application/x-tar",
		"tcap"	=>	"application/vnd.3gpp2.tcap",
		"tcl"	=>	"application/x-tcl",
		"teacher"	=>	"application/vnd.smart.teacher",
		"test"	=>	"test/mimetype",
		"tex"	=>	"application/x-tex",
		"texi"	=>	"application/x-texinfo",
		"texinfo"	=>	"application/x-texinfo",
		"text"	=>	"text/plain",
		"tfm"	=>	"application/x-tex-tfm",
		"tgz"	=>	"application/x-gzip,application/gzip",
		"tif"	=>	"image/tiff",
		"tiff"	=>	"image/tiff",
		"tmo"	=>	"application/vnd.tmobile-livetv",
		"torrent"	=>	"application/x-bittorrent",
		"tpl"	=>	"application/vnd.groove-tool-template",
		"tpt"	=>	"application/vnd.trid.tpt",
		"tr"	=>	"text/troff",
		"tra"	=>	"application/vnd.trueapp",
		"trm"	=>	"application/x-msterminal",
		"ts"	=>	"video/mp2t",
		"tsv"	=>	"text/tab-separated-values",
		"ttc"	=>	"application/x-font-ttf",
		"ttf"	=>	"application/x-font-ttf",
		"twd"	=>	"application/vnd.simtech-mindmapper",
		"twds"	=>	"application/vnd.simtech-mindmapper",
		"txd"	=>	"application/vnd.genomatix.tuxedo",
		"txf"	=>	"application/vnd.mobius.txf",
		"txt"	=>	"text/plain",
		"u32"	=>	"application/x-authorware-bin",
		"udeb"	=>	"application/vnd.debian.binary-package,application/x-debian-package",
		"ufd"	=>	"application/vnd.ufdl",
		"ufdl"	=>	"application/vnd.ufdl",
		"umj"	=>	"application/vnd.umajin",
		"unityweb"	=>	"application/vnd.unity",
		"uoml"	=>	"application/vnd.uoml+xml",
		"uri"	=>	"text/uri-list",
		"uris"	=>	"text/uri-list",
		"urls"	=>	"text/uri-list",
		"ustar"	=>	"application/x-ustar",
		"utz"	=>	"application/vnd.uiq.theme",
		"uu"	=>	"text/x-uuencode",
		"vcd"	=>	"application/x-cdlink",
		"vcf"	=>	"text/x-vcard",
		"vcg"	=>	"application/vnd.groove-vcard",
		"vcs"	=>	"text/x-vcalendar",
		"vcx"	=>	"application/vnd.vcx",
		"vis"	=>	"application/vnd.visionary",
		"viv"	=>	"video/vnd.vivo",
		"vor"	=>	"application/vnd.stardivision.writer",
		"vox"	=>	"application/x-authorware-bin",
		"vrml"	=>	"model/vrml",
		"vsd"	=>	"application/vnd.visio",
		"vsdx"	=>	"application/vnd.visio",
		"vsf"	=>	"application/vnd.vsf",
		"vss"	=>	"application/vnd.visio",
		"vssm"	=>	"application/vnd.visio",
		"vssx"	=>	"application/vnd.visio",
		"vst"	=>	"application/vnd.visio",
		"vstm"	=>	"application/vnd.visio",
		"vstx"	=>	"application/vnd.visio",
		"vsw"	=>	"application/vnd.visio",
		"vtu"	=>	"model/vnd.vtu",
		"vxml"	=>	"application/voicexml+xml",
		"w3d"	=>	"application/x-director",
		"wad"	=>	"application/x-doom",
		"wasm"	=>	"application/wasm",
		"wav"	=>	"audio/vnd.wav",
		"wax"	=>	"audio/x-ms-wax",
		"wbmp"	=>	"image/vnd.wap.wbmp",
		"wbs"	=>	"application/vnd.criticaltools.wbs+xml",
		"wbxml"	=>	"application/vnd.wap.wbxml",
		"wcm"	=>	"application/vnd.ms-works",
		"wdb"	=>	"application/vnd.ms-works",
		"weba"	=>	"audio/webm",
		"webm"	=>	"video/webm",
		"webp"	=>	"image/webp",
		"whl"	=>	"text/x-python",
		"wiz"	=>	"application/msword",
		"wks"	=>	"application/vnd.ms-works",
		"wm"	=>	"video/x-ms-wm",
		"wma"	=>	"audio/x-ms-wma",
		"wmd"	=>	"application/x-ms-wmd",
		"wmf"	=>	"application/x-msmetafile",
		"wml"	=>	"text/vnd.wap.wml",
		"wmlc"	=>	"application/vnd.wap.wmlc",
		"wmls"	=>	"text/vnd.wap.wmlscript",
		"wmlsc"	=>	"application/vnd.wap.wmlscriptc",
		"wmv"	=>	"video/x-ms-wmv",
		"wmx"	=>	"video/x-ms-wmx",
		"wmz"	=>	"application/x-ms-wmz",
		"woff"	=>	"font/woff",
		"woff2"	=>	"font/woff2",
		"wpd"	=>	"application/vnd.wordperfect",
		"wpl"	=>	"application/vnd.ms-wpl",
		"wps"	=>	"application/vnd.ms-works",
		"wqd"	=>	"application/vnd.wqd",
		"wri"	=>	"application/x-mswrite",
		"wrl"	=>	"model/vrml",
		"wsdl"	=>	"application/wsdl+xml",
		"wspolicy"	=>	"application/wspolicy+xml",
		"wtb"	=>	"application/vnd.webturbo",
		"wvx"	=>	"video/x-ms-wvx",
		"x32"	=>	"application/x-authorware-bin",
		"x3d"	=>	"application/vnd.hzn-3d-crossword",
		"x3f"	=>	"image/x-sigma-x3f",
		"xap"	=>	"application/x-silverlight-app",
		"xar"	=>	"application/vnd.xara",
		"xbap"	=>	"application/x-ms-xbap",
		"xbd"	=>	"application/vnd.fujixerox.docuworks.binder",
		"xbm"	=>	"image/x-xbitmap",
		"xdm"	=>	"application/vnd.syncml.dm+xml",
		"xdp"	=>	"application/vnd.adobe.xdp+xml",
		"xdw"	=>	"application/vnd.fujixerox.docuworks",
		"xenc"	=>	"application/xenc+xml",
		"xer"	=>	"application/patch-ops-error+xml",
		"xfdf"	=>	"application/vnd.adobe.xfdf",
		"xfdl"	=>	"application/vnd.xfdl",
		"xht"	=>	"application/xhtml+xml",
		"xhtml"	=>	"application/xhtml+xml",
		"xhvml"	=>	"application/xv+xml",
		"xif"	=>	"image/vnd.xiff",
		"xla"	=>	"application/vnd.ms-excel",
		"xlam"	=>	"application/vnd.ms-excel.addin.macroenabled.12",
		"xlb"	=>	"application/vnd.ms-excel",
		"xlc"	=>	"application/vnd.ms-excel",
		"xlm"	=>	"application/vnd.ms-excel",
		"xls"	=>	"application/vnd.ms-excel",
		"xlsb"	=>	"application/vnd.ms-excel.sheet.binary.macroenabled.12",
		"xlsm"	=>	"application/vnd.ms-excel.sheet.macroenabled.12",
		"xlsx"	=>	"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
		"xlt"	=>	"application/vnd.ms-excel",
		"xltm"	=>	"application/vnd.ms-excel.template.macroenabled.12",
		"xltx"	=>	"application/vnd.openxmlformats-officedocument.spreadsheetml.template",
		"xlw"	=>	"application/vnd.ms-excel",
		"xml"	=>	"application/rss+xml,application/xml",
		"xo"	=>	"application/vnd.olpc-sugar",
		"xop"	=>	"application/xop+xml",
		"xpdl"	=>	"application/xml",
		"xpi"	=>	"application/x-xpinstall",
		"xpm"	=>	"image/x-xpixmap",
		"xpr"	=>	"application/vnd.is-xpr",
		"xps"	=>	"application/vnd.ms-xpsdocument",
		"xpw"	=>	"application/vnd.intercon.formnet",
		"xpx"	=>	"application/vnd.intercon.formnet",
		"xsl"	=>	"application/xml",
		"xslt"	=>	"application/xslt+xml",
		"xsm"	=>	"application/vnd.syncml+xml",
		"xspf"	=>	"application/xspf+xml",
		"xul"	=>	"application/vnd.mozilla.xul+xml",
		"xvm"	=>	"application/xv+xml",
		"xvml"	=>	"application/xv+xml",
		"xwd"	=>	"image/x-xwindowdump",
		"xyz"	=>	"chemical/x-xyz",
		"yaml"	=>	"application/yaml",
		"yml"	=>	"application/yaml",
		"zabw"	=>	"application/x-abiword",
		"zaz"	=>	"application/vnd.zzazz.deck+xml",
		"zip"	=>	"application/zip,application/x-zip-compressed,application/zip-compressed",
		"zir"	=>	"application/vnd.zul",
		"zirz"	=>	"application/vnd.zul",
		"zmm"	=>	"application/vnd.handheld-entertainment+xml",
	);
	
	public function __construct($base,$params = null, $directory = null) {

		$this->base = $this->addTrailingSlash($base);

		//set the match pattern
		$tmp = str_replace(self::DS,'\\'.self::DS,$this->base);
		$this->pattern = "/^(".$tmp.")/";
		
		$defaultParams = array(
			'filesPermission'				=>	0777,
			'changeFilePermission'			=>	false,
			'delFolderAction'				=>	'delFolderAction',
			'delFileAction'					=>	'delFileAction',
			'createFolderAction'			=>	'createFolderAction',
			'uploadFileAction'				=>	'uploadFileAction',
			'maxFileSize' 					=>	3000000,
			'language' 						=>	'En',
			'allowedExtensions'				=>	'jpg,jpeg,png,gif,txt',
			'allowedMimeTypes'				=>	'',
			'fileUploadKey' 				=>	'userfile',
			'fileUploadBehaviour'			=>	'add_token', //can be none or add_token
			'fileUploadBeforeTokenChar'		=>	'_',
			'functionUponFileNane'			=>	'none',
			'createImage'					=>	false, // if it has to create the image after upload
			'createImageParams'				=>	null,
		);

		//set the $this->scaffold->params array
		if (is_array($params))
		{
			foreach ($params as $key => $value)
			{
				$defaultParams[$key] = $value;
			}
		}
		$this->params = $defaultParams;

		//instantiate the $_resultString object
		$stringClass = 'Lang_'.$this->params['language'].'_UploadStrings';
		if (!class_exists($stringClass))
		{
			$stringClass = 'Lang_En_UploadStrings';
		}
		$this->_resultString = new $stringClass();

		$this->setDirectory($directory);

	}

	//set a new value for one element of the $params array
	public function setParam($key,$value)
	{
		if (array_key_exists($key,$this->params))
		{
			$this->params[$key] = $value;
		}
	}

	//change a resulting string
	public function setString($key,$value)
	{
		$this->_resultString->string[$key] = $value;
	}
	
	//obtain the current directory
	public function setDirectory($directory = null)
	{
		$relDir = (strcmp(nullToBlank($directory),"") !== 0) ? $this->addTrailingSlash($directory) : null;
		$absDir = $this->addTrailingSlash($this->base.$directory);
		
		if (@is_dir($absDir))
		{
			if ($this->isValidFolder($absDir))
			{
				$this->directory = $relDir;
				return true;
			}
			else
			{
				$this->notice = $this->_resultString->getString('not-child');
			}
		}
		else
		{
			$this->directory = null;
			$this->notice = $this->_resultString->getString('not-dir');
		}
		return false;
	}
	
	//check if $folder is a folder and is subfolder of $this->base
	public function isValidFolder($folder)
	{
		if (@is_dir($folder))
		{
			$folder = $this->addTrailingSlash(realpath($folder));
			if ($this->isMatching($folder)) return true; 
		}
		return false;
	}

	public function isMatching($path)
	{
		if (preg_match($this->pattern,$path))
		{
			if (strstr($path,'..')) return false;
			return true;
		}
		return false;
	}

	public function getDirectory() {
		return $this->directory;
	}

	public function getBase()
	{
		return $this->base;
	}

	public function setBase($path)
	{
		$this->base = $this->addTrailingSlash($path);

		//set the match pattern
		$tmp = str_replace(self::DS,'\\'.self::DS,$this->base);
		$this->pattern = "/^(".$tmp.")/";
	}

	public function getSubDir() {
		return $this->subDir;
	}
	
	public function getRelSubDir()
	{
		return $this->relSubDir;
	}

	public function getFiles() {
		return $this->files;
	}

	public function getRelFiles()
	{
		return $this->relFiles;
	}

	public function getParentDir() {
		return $this->parentDir;
	}

	//add the trailing slash to the string
	protected function addTrailingSlash($string)
	{
		$finalChar = $string[strlen($string) - 1];
		if (strcmp($finalChar,self::DS) !== 0)
		{
			return $string.self::DS;
		}
		return $string;
	}

	protected function urlDeep($dir) { #funzione per creare l'indirizzo completo della cartella all'interno della quale voglio entrare
		#$dir:cartella all'interno della quale voglio entrare
		return $this->base.$this->directory.$dir.self::DS;
	}

	public function listFiles() { #creo la lista di file e cartelle all'interno della directory corrente
		$this->subDir = $this->relSubDir = $this->files = $this->relFiles = array();
		
		$items = scandir($this->base.$this->directory);
		foreach( $items as $this_file ) {
			if( strcmp($this_file,".") !== 0 && strcmp($this_file,"..") !== 0 ) {
				if (@is_dir($this->urlDeep($this_file))) {
					$this->subDir[] = $this_file;
					$this->relSubDir[] = $this->directory.$this_file;
				} else {
					$this->files[] = $this_file;
					$this->relFiles[] = $this->directory.$this_file;
				}
			}
		}
		//get the parent dir
		$this->parentDir();
	}

	//get the extension of the file
	public function getFileExtension($file)
	{
		return self::sFileExtension($file);
	}
	
	//get the extension of the file (static)
	public static function sFileExtension($file)
	{
		if (strstr($file,'.'))
		{
			$extArray = explode('.', $file);
			return strtolower(end($extArray));
		}
		return '';
	}

	// get mime types from extensions
	// $extensions: comma separated list of extensions
	public static function getMimeTypesFromExtensions($extensions)
	{
		$mimeTypes = array();
		$extensions = explode(',', (string) $extensions);

		foreach ($extensions as $extension)
		{
			$extension = ltrim(strtolower(trim($extension)), '.');

			if ($extension === '' || !isset(self::$extToMimeType[$extension]))
			{
				continue;
			}

			$currentMimeTypes = explode(',', self::$extToMimeType[$extension]);

			foreach ($currentMimeTypes as $mimeType)
			{
				$mimeType = trim($mimeType);

				if ($mimeType !== '' && !in_array($mimeType, $mimeTypes))
				{
					$mimeTypes[] = $mimeType;
				}
			}
		}

		return implode(',', $mimeTypes);
	}
	
	//get the file name without the extension
	public function getNameWithoutFileExtension($file)
	{
		if (strstr($file,'.'))
		{
			$copy = explode('.', $file);
			array_pop($copy);
			return implode('.',$copy);
		}
		return $file;
	}
	
	public static function isJpeg($ext)
	{
		return in_array(strtolower($ext),array("jpg","jpeg")) ? true : false;
	}
	
	public static function isImage($ext)
	{
		return in_array(strtolower($ext),array("jpg","jpeg","png")) ? true : false;
	}
	
	//get a not existing file name if the one retrieved from the upload process already exists in the current directory
	public function getUniqueName($file,$int = 0)
	{
		$fileNameWithoutExt = $this->getNameWithoutFileExtension($file);
		$extension = $this->getFileExtension($file);
		$token = $int === 0 ? null : $this->params['fileUploadBeforeTokenChar'].$int;

		$dotExt = strcmp($extension,'') !== 0 ? ".$extension" : null;
		
		$newName = $fileNameWithoutExt.$token.$dotExt;
		if (!file_exists($this->base.$this->directory.$newName))
		{
			return $newName;
		}
		else
		{
			return $this->getUniqueName($file,$int+1);
		}
		
	}

	//get a not existing folder name
	public function getUniqueFolderName($folder,$int = 0)
	{
		$token = $int === 0 ? null : $this->params['fileUploadBeforeTokenChar'].$int;
		
		$newName = $folder.$token;
		if (!@is_dir($this->base.$this->directory.$newName))
		{
			return $newName;
		}
		else
		{
			return $this->getUniqueFolderName($folder,$int+1);
		}
		
	}
	
	protected function parentDir() { #individuo la cartella madre
		$folders = explode(self::DS,nullToBlank($this->directory));
		array_pop($folders);
		array_pop($folders);
		$parent = implode(self::DS,$folders);
		$parent = (strcmp($parent,"") !== 0) ? $this->addTrailingSlash($parent) : null;

		if ($this->isValidFolder($this->base.$parent))
		{
			$this->parentDir = $parent;
		}
		else
		{
			$this->parentDir = null;
		}
	}

	//create the $name subfolder of the $this->directory folder
	public function createFolder($name) { #funzione per creare una cartella nella directory corrente
		$name = basename($name);
		if (strcmp(trim($name),'') !== 0)
		{
			if (is_writable($this->base.$this->directory))
			{
				$path = $this->base.$this->directory.$name;
				
				if ($this->isMatching($path))
				{
					if (!file_exists($path))
					{
						if (@mkdir($path,$this->params['filesPermission']))
						{
							@chmod($path, $this->params['filesPermission']);
							$this->notice = $this->_resultString->getString('executed');
							return true;
						}
						else
						{
							$this->notice = $this->_resultString->getString('error');
						}
					}
					else
					{
						$this->notice = $this->_resultString->getString('dir-exists');
					}
				}
				else
				{
					$this->notice = $this->_resultString->getString('not-child');
				}
			}
			else
			{
				$this->notice = $this->_resultString->getString('not-writable');
			}
		}
		else
		{
			$this->notice = $this->_resultString->getString('no-folder-specified');
		}
		return false;
	}

	//check if the $name folder is empty or not
	public function isEmpty($name)
	{
		$items = scandir($name);
		foreach( $items as $this_file ) {
			if( strcmp($this_file,".") !== 0 && strcmp($this_file,"..") !== 0 ) {
				return false;
			}
		}
		return true;
	}

	public function removeFile($name)
	{
		$name = basename($name);
		if (strcmp(trim($name),'') !== 0)
		{
			$path = $this->base.$this->directory.$name;
			if ($this->isMatching($path))
			{
				if ($this->removeAbsFile($path)) return true;
			}
			else
			{
				$this->notice = $this->_resultString->getString('not-child');
			}
		}
		else
		{
			$this->notice = $this->_resultString->getString('no-file-specified');
		}
		return false;
	}

	//remove the $name file
	protected function removeAbsFile($name)
	{
		if (strcmp(trim($name),'') !== 0)
		{
			if (is_writable($name))
			{
				if (@unlink($name))
				{
					$this->notice = $this->_resultString->getString('executed');
					return true;
				}
				else
				{
					$this->notice = $this->_resultString->getString('error');
				}
			}
			else
			{
				$this->notice = $this->_resultString->getString('not-writable-file');
			}
		}
		else
		{
			$this->notice = $this->_resultString->getString('no-file-specified');
		}
		return false;
	}

	public function removeFolder($name)
	{
		$name = basename($name);
		if (strcmp(trim($name),'') !== 0)
		{
			$dir = $this->base.$this->directory.$name;
			if ($this->isMatching($dir))
			{
				if ($this->removeAbsFolder($dir)) return true;
			}
			else
			{
				$this->notice = $this->_resultString->getString('not-child');
			}
		}
		else
		{
			$this->notice = $this->_resultString->getString('no-folder-specified');
		}
		return false;
	}
	
	//remove the $name folder
	protected function removeAbsFolder($name) {
		if (strcmp(trim($name),'') !== 0) {
			if (is_writable($name))
			{
				if ($this->isEmpty($name))
				{
					if (@rmdir($name))
					{
						$this->notice = $this->_resultString->getString('executed');
						return true;
					}
					else
					{
						$this->notice = $this->_resultString->getString('error');
					}
				}
				else
				{
					$this->notice = $this->_resultString->getString('not-empty');
				}
			}
			else
			{
				$this->notice = $this->_resultString->getString('not-writable');
			}
		}
		else
		{
			$this->notice = $this->_resultString->getString('no-folder-specified');
		}
		return false;
	}

	//remove all the files that are not inside the $list argument
	public function removeFilesNotInTheList($list = array())
	{
		$this->listFiles();
		$files = $this->getFiles();
		foreach ($files as $file)
		{
			if (!in_array($file,$list))
			{
				$this->removeFile($file);
			}
		}
	}
	
	// return the content type of the file $filename
	public function getContentType($filename)
	{
		//get the MIME type of the file
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$MIMEtype = finfo_file($finfo, $filename);
		$this->mimeType = $MIMEtype;
		
		return $MIMEtype;
	}
	
	//upload a file in the current directory
	//$fileName: name of the file
	public function uploadFile($fileName = null)
	{
		$userfile = $this->params['fileUploadKey'];
		
		if(isset($_FILES[$userfile]["name"]) && strcmp(trim($_FILES[$userfile]["name"]),"") !== 0)
		{
			// get MIME TYPES if empty
			if ($this->params['allowedExtensions'] != '' && $this->params['allowedMimeTypes'] == '')
				$this->params['allowedMimeTypes'] = self::getMimeTypesFromExtensions($this->params['allowedExtensions']);

			$nameFromUpload = basename($_FILES[$userfile]["name"]);

			$ext = $this->getFileExtension($nameFromUpload);
			$nameWithoutExtension = $this->getNameWithoutFileExtension($nameFromUpload);

			$dotExt = strcmp($ext,'') !== 0 ? ".$ext" : null;

			//check if the "functionUponFileNane" function exists
			if (!function_exists($this->params['functionUponFileNane'])) {
				throw new Exception('Error in <b>'.__METHOD__.'</b>: function <b>'.$this->params['functionUponFileNane']. '</b> does not exist');
			}

			//check if the fileinfo extension is loaded
			if (strcmp($this->params['allowedMimeTypes'],'') !== 0 and !extension_loaded('fileinfo')) {
				throw new Exception('Error in <b>'.__METHOD__.'</b>: no MIME type check is possible because the <b>fileinfo</b> extension is not loaded');
			}
			
			$nameWithoutExtension = call_user_func($this->params['functionUponFileNane'],$nameWithoutExtension);
			
			$fileName = isset($fileName) ? $fileName.$dotExt : $nameWithoutExtension.$dotExt;
			
			$this->fileName = $fileName;
			$this->ext = $ext;
			
			switch($this->params['fileUploadBehaviour'])
			{
				case 'none':
					break;
				case 'add_token':
					$this->fileName = $this->getUniqueName($this->fileName);
					$fileName = $this->fileName;
					break;
			}
		
			if(@is_uploaded_file($_FILES[$userfile]["tmp_name"])) {
				if ($_FILES[$userfile]["size"] <= $this->params['maxFileSize'])
				{
					//check the extension of the file
					$AllowedExtensionsArray = explode(',',$this->params['allowedExtensions']);
					
					if (strcmp($this->params['allowedExtensions'],'') === 0 or in_array($ext,$AllowedExtensionsArray))
					{
						if (strcmp($this->params['allowedMimeTypes'],'') !== 0)
						{
							//get the MIME type of the file
							$finfo = finfo_open(FILEINFO_MIME_TYPE);
							$MIMEtype = finfo_file($finfo, $_FILES[$userfile]["tmp_name"]);
							$this->mimeType = $MIMEtype;
						}
						
						$AllowedMimeTypesArray = explode(',',$this->params['allowedMimeTypes']);
						
						if (strcmp($this->params['allowedMimeTypes'],'') === 0 or in_array($MIMEtype,$AllowedMimeTypesArray))
						{
							//check if the file doesn't exist
							if (!file_exists($this->base.$this->directory.$fileName))
							{
								if (@move_uploaded_file($_FILES[$userfile]["tmp_name"],$this->base.$this->directory.$fileName))
								{
									if ($this->params['createImage'])
									{
										//create the image
										$basePath = $this->base.$this->directory;
										$thumb = new Image_Gd_Thumbnail($basePath, $this->params['createImageParams']);
										$thumb->render($fileName,$this->base.$this->directory.$fileName);
									}

									if ($this->params['changeFilePermission'])
									{
										@chmod($this->base.$this->directory.$fileName, $this->params['filesPermission']);
									}
									$this->notice = $this->_resultString->getString('executed');
									return true;
								}
								else
								{
									$this->notice = $this->_resultString->getString('error');
								}
							}
							else
							{
								$this->notice = $this->_resultString->getString('file-exists');
							}
						}
						else
						{
							$this->notice = $this->_resultString->getString('not-allowed-mime-type');
						}
					}
					else
					{
						$this->notice = $this->_resultString->getString('not-allowed-ext');
					}
				}
				else
				{
					$this->notice = $this->_resultString->getString('size-over');
				}
			}
			else
			{
				$this->notice = $this->_resultString->getString('no-upload-file');
			}
		}
		else
		{
			$this->notice = $this->_resultString->getString('no-upload-file');
		}
		return false;
	}

	//update the folder tree
	public function updateTree() {

		if (isset($_POST[$this->params['delFolderAction']])) {
			$this->removeFolder($_POST[$this->params['delFolderAction']]);
		}

		if (isset($_POST[$this->params['delFileAction']])) {
			$this->removeFile($_POST[$this->params['delFileAction']]);
		}

		if (isset($_POST[$this->params['createFolderAction']])) {
			$this->createFolder($_POST['folderName']);
		}

		if (isset($_POST[$this->params['uploadFileAction']])) {
			$this->uploadFile();
		}

	}
}
