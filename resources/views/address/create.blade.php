<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>BW address book</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
</head>
<body>
<div class="container-fluid">
    <div class="alert alert-danger {{ $errors->any() ? "show" : "collapse" }}">
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif
    </div>

    <h1>Add new address</h1>
    <a href="{{ route('address.index') }}" class="btn btn-success">List Address Book</a>
    <div class="col-12 col-md-6 collapse" id="suggestions-container">
            <h3>Suggestions</h3>
            <p>We have a suggestion for your address. Please accept the suggestion or continue with the original address.</p>
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">q</th>
                    <th scope="col">Original</th>
                    <th scope="col">Suggestion</th>
                </tr>
                </thead>
                <tbody>
                <tr id="street-compare">
                    <th scope="row">Street</th>
                    <td>1</td>
                    <td>2</td>
                </tr>
                <tr id="city-compare">
                    <th scope="row">City</th>
                    <td></td>
                    <td></td>
                </tr>
                <tr id="state-compare">
                    <th scope="row">State</th>
                    <td></td>
                    <td></td>
                </tr>
                <tr id="zip-compare">
                    <th scope="row">Zip</th>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <th scope="row"></th>
                    <td>
                        <button type="button" class="btn btn-primary" id="reject-suggestion">Keep Original</button>
                    </td>
                    <td>
                        <button type="button" class="btn btn-primary" id="accept-suggestion">Accept Suggestion</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    <form action="{{ route('address.store') }}" method="POST" id="address-form">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" name="name" id="name" placeholder="John Smith" value="{{ old('name') }}" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" name="phone" id="phone" placeholder="0123456789" value="{{ old('phone') }}" required>
            </div>
            <div class="mb-3">
                <label for="street" class="form-label">Street</label>
                <input type="text" class="form-control" name="street" id="street" placeholder="123 Main St" value="{{ old('street') }}" required>
            </div>
            <div class="mb-3">
                <label for="city" class="form-label">City</label>
                <input type="text" class="form-control" name="city" id="city" placeholder="New York" value="{{ old('city') }}" required>
            </div>
            <div class="mb-3">
                <label for="state" class="form-label">State</label>
                <input type="text" class="form-control" name="state" id="state" placeholder="NY" value="{{ old('state') }}" required>
            </div>
            <div class="mb-3">
                <label for="zip" class="form-label">Zip</label>
                <input type="text" class="form-control" name="zip" id="zip" placeholder="10001" value="{{ old('zip') }}" required>
            </div>
            <div class="mb-3">
                <label for="country" class="form-label">Country</label>
                <input type="text" class="form-control" name="country" id="country" placeholder="USA" readonly>
            </div>
            <button type="button" id="add-address-button" class="btn btn-primary">Add Address</button>
        </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script>
    const form = $('#address-form');
    const alert = $('.alert');
    const formKeys = ['street', 'city', 'state', 'zip'];
    let originalData = {};
    let suggestedData = {};
    $('#add-address-button').on('click', (e) => {
        e.preventDefault();
        const formData = new FormData(form[0]);
        originalData = formKeys.reduce((acc, key) => {
            acc[key] = formData.get(key);
            return acc;
        }, {});
        const apiQuery = formKeys.map(key => `${key}=${encodeURIComponent(originalData[key])}`).join('&');

        fetch('https://us-street.api.smartystreets.com/street-address?key=158748789304504630&match=invalid&street2=&' + apiQuery)
            .then(response => {
                if (!response.ok || response.status !== 200) {
                    Promise.reject(response);
                }
                return response.json();
            })
            .then(data => {
                if (data.length > 0) {
                    const address = data[0];
                    suggestedData = {
                        'street': address.delivery_line_1,
                        'city': address.components.city_name,
                        'state': address.components.state_abbreviation,
                        'zip': address.components.zipcode
                    };

                    let hasDifference = false;
                    formKeys.forEach(key => {
                        const original = originalData[key];
                        const suggested = suggestedData[key];
                        const originalCell = $(`#${key}-compare td:nth-child(2)`);
                        const suggestedCell = $(`#${key}-compare td:nth-child(3)`);
                        originalCell.text(original);
                        suggestedCell.text(suggested);

                        if (original !== suggested) {
                            hasDifference = true;
                            $(`#${key}-compare`).addClass('table-danger');
                        } else {
                            $(`#${key}-compare`).removeClass('table-success');
                        }
                    });
                        if (hasDifference) {
                            $('#suggestions-container').removeClass('collapse');
                        } else {
                            form.submit();
                        }
                }
            })
            .catch(error => {
                alert.text('There was an error validating your address. Please try again.');
                alert.removeClass('collapse');
            });
    });
    $('#reject-suggestion').on('click', () => {
        form.submit()
    });
    $('#accept-suggestion').on('click', () => {
        formKeys.forEach(key => {
            $(`#${key}`).val(suggestedData[key]);
        });
        form.submit();
    });
</script>
</body>
