<?php
$connected_opportunities = get_connected_opportunities();
?>
  <table width='100%' border='0'>
    <tbody>
    <tr>
      <th>&emsp;</th>
      <th>Opportunity Name</th>
      <th>Description</th>
      <th>Hours Logged</th>
    </tr>
<?php
foreach($connected_opportunities as $row) {
  if ($row->{'19'}->value != "") {
?>
    <tr>
      <td><a href=https://calvol.usdr.dev/volunteer-dashboard/record-shifts?location=<?php echo urlencode($row->{'24'}->value); ?>>Record hours</a></td>
      <td><?php echo $row->{'24'}->value; ?></td>
      <td><?php echo $row->{'20'}->value; ?></td>
      <td><?php echo get_hours_logged_for_opportunity($row->{'24'}->value); ?></td>
      <td>&nbsp;</td>
    </tr>
<?php
  }
}
?>
  </tbody>
</table>
