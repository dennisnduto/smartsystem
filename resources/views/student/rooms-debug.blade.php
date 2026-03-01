@extends('layouts.minimal')

@section('content')
<div>
  <h1>DEBUG TEST</h1>
  <p>If you can see this, the view is working</p>
  <p>The number 1 should NOT appear below this line</p>
  <hr>
  <?php // No PHP code that could output 1 ?>
  <p>End of test</p>
</div>
@endsection
