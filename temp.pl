foreach (sort {$a<=>$b} keys (%$AmazonItems))
{
  my $InfoID = $_;
  my $ASIN = $AmazonItems->{$InfoID}->{'ASIN'};
  my $Country = $AmazonItems->{$InfoID}->{'Country'};
  if ($ASIN ne "")
  {
    $Info = &get_amazon_info($Country, $ASIN);
    my $CurrentPrice = $Info->{'Price'};
    if ($Price)
    {
      $LastScanUpdated->{$InfoID} = "1";
      if ($CurrentPrice != $AmazoneItems->{$InfoID}->{'LastValue'})
      {
        $PriceUpdated->{$InfoID} = $CurrentPrice;
      }
    }

    my $Shipping = "Free shipping";
    if ($Info->{'shipping'} ne 0)
    {
      $Shipping = "Charged!";
      $CurrentPrice = 0;
    }
    my $Pieces = $LegoSets->{$LegoId}->{'Pieces'};
    my $MSRP = $LegoSets->{$LegoId}->{$Country.'Price'};
    my $Theme = $LegoSets->{$LegoId}->{'ETheme'};
    my $Title = $LegoSets->{$LegoId}->{'ETitle'};

	my $CNTitle = $LegoSets->{$LegoID}->{'CNTitle'};
    utf8::decode($CNTitle);
	if ($CNTitle ne "")
	{
	  $CNTitle = "(".$CNTitle.") ";
	}

    if (($CurrentPrice > 0) and ($Pieces > 0) and ($MSRP > 0))
    {
      $price = $CurrentPrice;
      $delta = sprintf("%.2f", $price - $MSRP);
      $rate = sprintf("%.2f", $price / $MSRP * 100);
      $discount = sprintf("%.0f", $rate);
      my $ppp = sprintf("%.2f", $price / $Pieces * 100);

      switch($country)
      {
        case 'CN':
          my $url = "";
          break;
        case "US":
          my $url = "";
          break;
        default:
      }

      if (($rate <= 80) || ($delta <= -7))
      {



        my $link = "http://www.amazon.com/gp/product/$asin/ref=as_li_ss_tl?ie=UTF8&tag=". myAssociateTag ."&linkCode=as2&camp=217145&creative=399373&creativeASIN=$asin";
        $link = LegoDB::Shorten($link);
        $content = "LEGO $legoid - $theme: $title $cntitle"."$discount"."折特价中, amazon.com仅售\$$price(原价\$$msrp)：$link";
        $content2 = "价格更新至"."$discount"."折, amazon.com仅售\$$price(原价\$$msrp)：$link";
        if ($published < 3)
        {
          if (!defined($publish->{$legoid}))
          {
            my $weiboID = LegoDB::PubWeibo($legoid, $content);
            if (defined($weiboID))
            {
              $published++;
              &update_publish_table($legoid, $price, $weiboID);
            }
          }
          elsif ($price < $publish->{$legoid}->{'Price'})
          {
            #print Dumper($publish->{$legoid}->{'WeiboID'});
            my $weiboID = LegoDB::ReplyWeibo($publish->{$legoid}->{'WeiboID'}, $content2);
            if (defined($weiboID))
            {
              $published++;
              &update_publish_table($legoid, $price, $weiboID);
            }
          }
        }
        else
        {
           &update_amazoninfo_lastscan($lastscanupdated);
           &update_amazoninfo_price($priceupdated);
           die "too much posts.";
        }
      }
    }
  }
}
&update_amazoninfo_lastscan($lastscanupdated);
&update_amazoninfo_price($priceupdated);

sub update_amazoninfo_price
{
  $items = shift;
  my $sqlstr = "";
  foreach (sort {$a<=>$b} keys (%$items))
  {
    $itemid = $_;
    $price = $items->{$itemid};
    $sqlstr = "UPDATE amazoninfo SET `price` = ".$dbh->quote($price)." WHERE `legoid` = ".$dbh->quote($itemid)." LIMIT 1;";
    $dbh->do($sqlstr);
  }
  if (DEBUG) {print strftime("%Y%m%d %H:%M:%S", localtime())." ".keys(%$items)." of item price updated.\r\n";}
}

sub update_amazoninfo_lastscan
{
  $items = shift;
  my $itemids = "";
  foreach (sort {$a<=>$b} keys (%$items))
  {
    $itemids = $itemids.",".$_;
  }
  $itemids =~ s/^,+//;
  $itemids =~ s/,+$//;
  my $sqlstr = "UPDATE amazoninfo SET `lastscan` = NOW() WHERE `legoid` IN (".$itemids.");";
  if (DEBUG) {print strftime("%Y%m%d %H:%M:%S", localtime())." ".keys(%$items)." scantime updated.\r\n";}
  if ($itemids)
  {
    $dbh->do($sqlstr);
  }
}


sub update_publish_table
{
my $legoid = shift;
my $price = shift;
my $weiboID = shift;


my $sqlstr = "INSERT INTO PublishWeibo (LegoID,Price,Datetime,WeiboID) VALUES (".$dbh->quote($legoid).", ".$dbh->quote($price).", NOW(), ".$dbh->quote($weiboID).");";
$dbh->do($sqlstr);

}

sub get_amazon_info
{
  my $country = shift;
  my $itemId = shift;

  switch ($country)
  {
  case "US":
		my $helper = new RequestSignatureHelper (
			+RequestSignatureHelper::kAWSAccessKeyId => usAWSId,
			+RequestSignatureHelper::kAWSSecretKey => usAWSSecret,
			+RequestSignatureHelper::kEndPoint => usEndPoint,
		);

		my $request = {
			Service => 'AWSECommerceService',
			Operation => 'ItemLookup',
			ItemId => $itemId,
			ResponseGroup => 'ItemAttributes,OfferFull',
			AssociateTag => usAssociateTag,
		};
		break;
  case "CN":
		my $helper = new RequestSignatureHelper (
			+RequestSignatureHelper::kAWSAccessKeyId => cnAWSId,
			+RequestSignatureHelper::kAWSSecretKey => cnAWSSecret,
			+RequestSignatureHelper::kEndPoint => cnEndPoint,
		);

		my $request = {
			Service => 'AWSECommerceService',
			Operation => 'ItemLookup',
			ItemId => $itemId,
			ResponseGroup => 'ItemAttributes,OfferFull',
			AssociateTag => cnAssociateTag,
		};
		break;
  default:
  }
my $signedRequest = $helper->sign($request);
my $queryString = $helper->canonicalize($signedRequest);
my $url = "http://" . myEndPoint . "/onca/xml?" . $queryString;
my $ua = new LWP::UserAgent();
my $response = $ua->get($url);
my $content = $response->content();

my $xmlParser = new XML::Simple();
my $xml = $xmlParser->XMLin($content);

my $title = $xml->{Items}->{Item}->{ItemAttributes}->{Title};
my $legoid = $xml->{Items}->{Item}->{ItemAttributes}->{PartNumber};
my $merchant = $xml->{Items}->{Item}->{Offers}->{Offer}->{Merchant}->{Name};
my $price = $xml->{Items}->{Item}->{Offers}->{Offer}->{OfferListing}->{Price}->{FormattedPrice};
if ($price)
{
  $price =~ s/\$//g;
}
my $shipping = $xml->{Items}->{Item}->{Offers}->{Offer}->{OfferListing}->{IsEligibleForSuperSaverShipping};
my $amazon_info;

  if ($response->is_success())
  {
      $amazon_info->{'apiurl'} = $url;
      $amazon_info->{'legoid'} = $legoid;
      $amazon_info->{'asin'} = $itemId;
      $amazon_info->{'title'} = $title;
      $amazon_info->{'merchant'} = $merchant;
      $amazon_info->{'price'} = $price;
    if ($shipping)
    {
      $amazon_info->{'shipping'} = 0;
    }
    else
    {
      $amazon_info->{'shipping'} = 10;
    }
  }
  else
  {
    my $error = findError($xml);
    if (defined $error) { 
        print "Error: " . $error->{Code} . ": " . $error->{Message} . "\n";
    } else {
        print "Unknown Error!\n";
    }
    $amazon_info = '';
  }

return $amazon_info;
}

sub findError {
    my $xml = shift;
    
    return undef unless ref($xml) eq 'HASH';
    
    if (exists $xml->{Error}) { return $xml->{Error}; };
    
    for (keys %$xml) {
        my $error = findError($xml->{$_});
        return $error if defined $error;
    }
    
    return undef;
}