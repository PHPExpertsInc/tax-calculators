<?php
include '_header.tpl.php';
?>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=212893132177164";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
		<div id="content">
			<p>This calculator is a <a rel="external" href="http://www.phpu.cc/?referrer=project">PHP University</a> Project.<br/>
				You can watch a time-lapse video of its construction on <a rel="external" href="http://www.youtube.com/watch?v=xJPf7XGDLqo">YouTube</a>.
			</p>

			<hr/>

			<p><strong>NOTE:</strong> This U.S. Federal Individual income tax calculator shows the estimated personal taxes owed by both individuals and married couples, whether employeed or self-employeed.</p>
			<p>The 2013 income tax calculations are what <em>the law currently specifies</em> will be owed by Americans in the year 2013, unless Congress (beleaguered by the debt ceilling,
			a Presidential election, and massive looming automatic budget cuts) has the time and wherewithal to change the law.</p>
			<p>The numbers are pretty troubling: <a href="http://www.phpu.cc/taxes/individual/?income=50000&deductions=0&mode=Married%3A+Joint&employment_type=Employee"><strong>24% more taxes for the typical family</strong></a>. Leading economists have started stating that if the fiscal cliff isn't resolved, it could lead to a <a rel="external" href="http://www.wyattresearch.com/article/the-biggest-financial-scam-going/28542"><strong>20% market correction</strong></a> by March 2013.</p>
			<p>But this is just part of the looming <em><a rel="external" href="http://www.youtube.com/watch?v=7xPMDANpuN0&feature=related">Fiscal Cliff</a></em> [<a rel="external" href="http://www.youtube.com/watch?v=7xPMDANpuN0&feature=related">youtube.com</a>] that awaits us, that could be 
			<a rel="external" href="http://www.permamarks.net/grabbed_urls/OQhBYg/news.yahoo.com_7/news.yahoo.com/analysis-fiscal-cliff-could-hit-174247545.html">much more worse</a> than people have predicted so far.
			It is speculated by leading theorists and politicians that there is a <strong><a rel="external" href="http://thehill.com/blogs/on-the-money/domestic-taxes/251095-gop-fears-reelected-obama-would-have-leverage-to-raise-taxes">70% chance the taxes will be owed if Obama is re-elected</a></strong>, and 30% if Romney is.</p>
			<div>
				<div class="fb-like" style="float: left; margin: 0 20px 0 20px" data-href="http://www.phpu.cc/taxes/individual/" data-send="true" data-layout="button_count" data-width="450" data-show-faces="true" data-font="tahoma"></div>
				<div style="float: left"><a href="https://twitter.com/share" class="twitter-share-button" data-url="http://www.phpu.cc/taxes/individual/" data-lang="en">Tweet</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></div>
				<br/>
			</div>
<?php if (!empty($errorMessage)) { ?>
			<p>It took me 3 hours and 40 minutes to create this calculator. You can 
			<a href="http://www.youtube.com/watch?v=xJPf7XGDLqo&amp;feature=plcp" onclick="window.location='http://www.wisdomproject.cc/url/1f'; return false;"><strong>watch it being made</strong></a>,
			time lapsed to just over 7 minutes. It's pretty cool ;-)</p>
			<div class="errorMessage">
				<h3>ERROR:</h3>
				<div><?php echo $errorMessage; ?></div>
			</div>
<?php } ?>
			<img src="/images/calculator-195x164.jpg" alt="calculator" style="float: right"/>
			<form method="get" style="float: left">
				<table id="income_data_table">
					<tr>
						<th><label for="country">Country: </label></th>
						<td>
							<select name="country" id="country">
								<option<?php echo $country === 'US' ? ' selected="selected"' : ''; ?>>US</option>
								<option<?php echo $country === 'CA' ? ' selected="selected"' : ''; ?>>CA</option>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="income">Gross income: </label></th>
						<td><input type="text" name="income" id="income" value="<?php echo $e_income; ?>"/></td>
					</tr>
					<tr>
						<th><label for="deductions"><a rel="external" href="http://www.permamarks.net/grabbed_urls/OQhBYg/hr.cch.com_22/hr.cch.com/news/payroll/091712a.html">Deductions</a> ($0 = standard): </label></th>
						<td><input type="text" name="deductions" id="deductions" value="<?php echo $e_deductions; ?>"/></td>
					</tr>
					<tr>
						<th><label for="mode">Mode:</label></th>
						<td>
							<select name="mode" id="mode">
								<option value="<?php echo API_Types_TaxMode::SINGLE; ?>"<?php echo (!empty($taxMode) && $taxMode == API_Types_TaxMode::SINGLE) ? ' selected="selected"' : ''; ?>>Single</option>
								<option value="<?php echo API_Types_TaxMode::JOINT; ?>"<?php echo (!empty($taxMode) && $taxMode == API_Types_TaxMode::JOINT) ? ' selected="selected"' : ''; ?>>Married: Joint (US Only)</option>
								<option value="<?php echo API_Types_TaxMode::SPOUSE_AMOUNT; ?>"<?php echo (!empty($taxMode) && $taxMode == API_Types_TaxMode::SPOUSE_AMOUNT) ? ' selected="selected"' : ''; ?>>Spouse Amount (CA Only)</option>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="employment_type">Employment type: </label></th>
						<td>
							<select name="employment_type" id="employment_type">
								<option value="<?php echo API_Types_Employment::EMPLOYEE; ?>"
									<?php echo (!empty($employmentType) && $employmentType == API_Types_Employment::EMPLOYEE) ? ' selected="selected"' : ''; ?>>Employee</option>
								<option value="<?php echo API_Types_Employment::SELF; ?>"
									<?php echo (!empty($employmentType) && $employmentType == API_Types_Employment::SELF) ? ' selected="selected"' : ''; ?>>Self-Employeed</option>
							</select>
						</td>
					</tr>
					<tr>
						<td></td>
						<td colspan="2">
							<input type="submit" class="green" value="Calculate" style="width: 6em; text-align: center"/>
						</td>
					</tr>
				</table>
			</form>
			<br style="clear: both"/>
			<p>Check out my latest guide: <strong><a rel="external" href="http://www.amazon.com/The-College-Alternative-Self-Sufficiency-ebook/dp/B00AT3NQ4U?SubscriptionId=02878DQT5PCQS4ZJVF82&tag=thewispro-20&linkCode=xm2&camp=2025&creative=165953&creativeASIN=B00AT3NQ4U" onclick="window.open('http://www.wisdomproject.cc/url/2o'); return false;">The College Alternative: A Path To Retiring By 50 Without Needing a Job or Degree</strong></a>.<img src="http://www.assoc-amazon.com/e/ir?t=thewispro-20&l=as2&o=1&a=B00AT3NQ4U" width="1" height="1" alt="" style="border:none !important; margin:0px !important;" /></p>
<?php
	if (!empty($taxReport))
	{
?>
			<div id="tax_liability">
				<h2>Tax Liability</h2>
			<h3>Yes! Congress took action and <span style="color: red">*permanently*</span>
			    fixed <a rel="external" href="http://www.permamarks.net/grabbed_urls/OQhBYg/www.marketwatch.com_211.htmlz">the Alternative Minimum Tax</a>, which would have been <a rel="external" href="http://www.forbes.com/sites/anthonynitti/2012/12/12/while-the-fiscal-cliff-keeps-you-distracted-the-amt-will-rob-you-blind/">much worse</a> than what's listed.</h3>
			<h2 style="color: red">2013-01-03 14:02 EST UPDATED FOR THE FISCAL CLIFF COMPROMISE~! The calculator is now using <a rel="external" href="http://www.permamarks.net/grabbed_urls/OQhBYg/www.mydollarplan.com_216.htmlz">the final tax rates</a> according to the IRS.</h2>
			<div id="showEquationsBox"><a href="javascript:document.getElementById('equations').style.display = 'block'; document.getElementById('showEquationsBox').style.display='none'">+ Show equations</a></div>
			<div id="equations" style="display: none">
				<div><a href="javascript:document.getElementById('showEquationsBox').style.display = 'block'; document.getElementById('equations').style.display = 'none'">- Hide equations</a></div>
				<?php echo $debugInfo; ?>
			</div>
				<table class="report">
					<tr>
						<td>&nbsp;</td>
						<th style="text-align: right"><?php echo $years[0]; ?></th>
						<th style="text-align: right"><?php echo $years[1]; ?></th>
					</tr>
					<tr>
						<th>Mode: </th>
						<td colspan="2" style="text-align: center">
							<?php echo $e_mode; ?>
						</td>
					</tr>
					<tr>
						<th>Gross Income:</th>
						<td><?php echo $taxReport[$years[0]]->grossIncome->format(); ?></td>
						<td><?php echo $taxReport[$years[1]]->grossIncome->format(); ?></td>
					</tr>
					<tr>
						<th>Federal Income Tax:</th>
						<td><?php echo $taxReport[$years[0]]->federalIncomeTax; ?></td>
						<td><?php echo $taxReport[$years[1]]->federalIncomeTax; ?></td>
					</tr>
					<?php if ($country === 'US') { ?>
					<tr>
						<th>Social Security Tax:</th>
						<td><?php echo $taxReport[$years[0]]->ssiTax; ?></td>
						<td><?php echo $taxReport[$years[1]]->ssiTax; ?></td>
					</tr>
					<tr>
						<th>Medicare Tax:</th>
						<td><?php echo $taxReport[$years[0]]->medicareTax; ?></td>
						<td><?php echo $taxReport[$years[1]]->medicareTax; ?></td>
					</tr>
					<tr>
						<th><a rel="external" href="http://benefitslink.com/articles/guests/washbull110404a.html">Additional Medicare Tax</a>:</th>
						<td><?php echo $taxReport[$years[0]]->addMedicareTax; ?></td>
						<td><?php echo $taxReport[$years[1]]->addMedicareTax; ?></td>
					</tr>
					<?php } elseif ($country === 'CA') { ?>
					<tr>
						<th>Canada Pension Plan:</th>
						<td><?php echo $taxReport[$years[0]]->cppContribution; ?></td>
						<td><?php echo $taxReport[$years[1]]->cppContribution; ?></td>
					</tr>
					<tr>
						<th>Employment Insurance:</th>
						<td><?php echo $taxReport[$years[0]]->employmentIns; ?></td>
						<td><?php echo $taxReport[$years[1]]->employmentIns; ?></td>
					</tr>
					<?php } ?>
					<tr>
						<th>Total Tax Liability: </th>
						<td><?php echo $taxReport[$years[0]]->totalTaxes; ?></td>
						<td><?php echo $taxReport[$years[1]]->totalTaxes; ?></td>
					</tr>
					<tr>
						<th>Net Income:</th>
						<td><?php echo $taxReport[$years[0]]->netIncome; ?></td>
						<td><?php echo $taxReport[$years[1]]->netIncome; ?></td>
					</tr>
				</table>
			</div>
<?php
	}
?>
			<p>Become a part of the solution. Share this with friends, family, everyone.</p>
<!--
			<p>Consider donating a $1. 100% of all donations will go to advertising to get the message out.
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="NTP23NVYSZ6WY">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
			</p>
-->
			<h3><a rel="external" href="http://www.phpu.cc/taxes/corporate/" onclick="window.location='http://www.wisdomproject.cc/url/1d'; return false;">Federal Corporate Income Tax Calculator</a></h3>
		</div>
		<div id="amzn_box">
			<iframe src="http://rcm.amazon.com/e/cm?lt1=_blank&bc1=000000&IS2=1&bg1=FFFFFF&fc1=000000&lc1=0000FF&t=thewispro-20&o=1&p=8&l=as4&m=amazon&f=ifr&ref=ss_til&asins=B009HBCZPQ" style="width:120px;height:240px;" scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>
		</div>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-34757797-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
<br style="clear: both"/>
<!-- Begin Disqus -->
    <div id="disqus_thread"></div>
    <script type="text/javascript">
        /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
        var disqus_shortname = 'phpu'; // required: replace example with your forum shortname

        /* * * DON'T EDIT BELOW THIS LINE * * */
        (function() {
            var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
            dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
        })();
    </script>
    <noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
    <a href="http://disqus.com" class="dsq-brlink">comments powered by <span class="logo-disqus">Disqus</span></a>
<!-- End Disqus -->
<div class="fb-comments" data-href="http://www.phpu.cc/taxes/individual/" data-num-posts="5" data-width="470"></div>
<?php
include '_footer.tpl.php';
