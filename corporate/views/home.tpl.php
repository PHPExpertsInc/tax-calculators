<?php
include '_header.tpl.php';
?>
		<div id="content">
		<p>This calculator is a <a href="http://www.phpu.cc/?referrer=project">PHP University</a> Project.</p>
		<p>It took me 4 hours and 30 minutes to create this calculator. You can
		<a href="http://youtu.be/uCQYmNoICmg?desc=PHPU+Lesson+5+Corp+Income+Tax" onclick="window.location='http://www.wisdomproject.cc/url/1g'; return false;"><strong>watch it being made</strong></a>,
		time lapsed to just over 17 minutes. It's pretty cool ;-)</p>
		<hr/>

		<p>This app is designed to calculate the estimated income tax of a corporation.</p>
		<h4>Disclaimer</h4>
		<p>This app is for educational purposes only. It is by no means a substitute for proper tax accounting services!</p>
		<p><em>Note: Because of <a href="http://workforcesecurity.doleta.gov/unemploy/uitaxtopic.asp">Congressional bungling</a>, the Unemployment Taxrate
			for 2011 is overstated (usually by just 0.2%).</em></p>
<?php
if (isset($e_errorMessage)):
?>
		<div id="error_messag">
			<?php echo $e_errorMessage; ?>
		</div>
<?php endif; ?>
		<div id="income_form">
			<img src="http://www.phpu.cc/images/calculator-195x164.jpg" alt="calculator" style="float: right"/>
			<form method="get">
				<table id="income_data_table">
					<tr>
						<th><label for="gross_income">Gross income:</label></th>
						<td><input type="text" name="gross_income" value="<?php echo $e_grossIncome; ?>"/></td>
					</tr>
					<tr>
						<th><label for="expenses">Expenses:</label></th>
						<td><input type="text" name="expenses" value="<?php echo $e_expenses; ?>"/></td>
					</tr>
					<tr>
						<th><label for="wages">Wages:</label></th>
						<td><input type="text" name="wages" value="<?php echo $e_wages; ?>"/></td>
					</tr>
					<tr>
						<th><label for="expenses">No. of Employees:</label></th>
						<td><input type="text" name="num_of_employees" value="<?php echo $e_numOfEmployees; ?>"/></td>
					</tr>
					<tr>
						<td colspan="2"><input class="green" type="submit" value="Calculate"></td>
					</tr>
				</table>
			</form>
		</div>
<?php
if ($fedTaxes instanceof FedTaxes):
?>
		<div id="taxes_data">
			<h3>Tax Information</h3>
			<table class="report">
				<tr>
					<td>&nbsp;</td>
					<th style="text-align: right">2012</th>
				</tr>
				<tr>
					<th>Income Tax: </th>
					<td><?php echo $fedTaxes->income; ?></td>
				</tr>
				<tr>
					<th>Social Security Tax:</th>
					<td><?php echo $fedTaxes->ssi; ?></td>
				</tr>
				<tr>
					<th>Medicare Tax:</th>
					<td><?php echo $fedTaxes->medicare; ?></td>
				</tr>
				<tr>
					<th><a href="http://workforcesecurity.doleta.gov/unemploy/uitaxtopic.asp">Unemployment Tax:</a></th>
					<td><?php echo $fedTaxes->unemployment; ?></td>
				</tr>
				<tr>
					<th>Total Liability:</th>
					<td><?php echo $fedTaxes->total; ?></td>
				</tr>
			</table>
		</div>
<?php endif; ?>
		<h3><a href="http://www.phpu.cc/repo/phpu-training/7.indv-income-tax-calc/" onclick="window.location='http://www.wisdomproject.cc/url/1e'; return false;">2012 2013 Federal Individual Income Tax Calculator</a></h3>
		</div>
<!-- Begin: adBrite, Generated: 2012-09-16 3:09:03  -->
<script type="text/javascript">
var AdBrite_Title_Color = '0000FF';
var AdBrite_Text_Color = '000000';
var AdBrite_Background_Color = 'FFFFFF';
var AdBrite_Border_Color = 'CCCCCC';
var AdBrite_URL_Color = '008000';
var AdBrite_Page_Url = '';
try{var AdBrite_Iframe=window.top!=window.self?2:1;var AdBrite_Referrer=document.referrer==''?document.location:document.referrer;AdBrite_Referrer=encodeURIComponent(AdBrite_Referrer);}catch(e){var AdBrite_Iframe='';var AdBrite_Referrer='';}
</script>
<span style="white-space:nowrap;"><script type="text/javascript">document.write(String.fromCharCode(60,83,67,82,73,80,84));document.write(' src="http://ads.adbrite.com/mb/text_group.php?sid=2213284&zs=3732385f3930&ifr='+AdBrite_Iframe+'&ref='+AdBrite_Referrer+'&purl='+encodeURIComponent(AdBrite_Page_Url)+'" type="text/javascript">');document.write(String.fromCharCode(60,47,83,67,82,73,80,84,62));</script>
<a target="_top" href="http://www.adbrite.com/mb/commerce/purchase_form.php?opid=2213284&afsid=1"><img src="http://files.adbrite.com/mb/images/adbrite-your-ad-here-leaderboard.gif" style="background-color:#CCCCCC;border:none;padding:0;margin:0;" alt="Your Ad Here" width="14" height="90" border="0" /></a></span>
<!-- End: adBrite -->
                <div id="amzn_box">
				<iframe src="http://rcm.amazon.com/e/cm?lt1=_blank&bc1=000000&IS2=1&bg1=FFFFFF&fc1=000000&lc1=0000FF&t=thewispro-20&o=1&p=8&l=as4&m=amazon&f=ifr&ref=ss_til&asins=B009CCVMNQ" style="width:120px;height:240px;" scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>

                </div>
<?php
include '_footer.tpl.php';
