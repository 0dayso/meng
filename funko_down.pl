#!/usr/bin/perl
use Data::Dumper;
use XML::Simple;

my $xmlParser = new XML::Simple();
my $content = "pop_vinyl.xml";
open(IN, "< $content") or die("Error: could not open ");
my $xml = $xmlParser->XMLin($content);
close(IN);

foreach $item ($xml->{item}->)
{
	print(Dumper($item));
	break;
	#print($item->{title});
}
