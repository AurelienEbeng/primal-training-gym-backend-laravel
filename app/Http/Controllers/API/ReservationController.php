<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Services\ReservationService;


class ReservationController extends Controller
{
    protected $service;

    public function __construct(ReservationService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $reservations = Reservation::all()->map(function ($reservation) {
            return [
                "id" => $reservation->id,
                "name" => $reservation->name,
                "classType" => $reservation->classType,
                "time" => $reservation->time,
                "date" => $reservation->date,
            ];
        });

        return response()->json($reservations);
    }

    public function store(Request $request)
    {

        $data = $this->service->updateMutableDataWithCorrectTimeFormat($request->all());

        $request->merge($data);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'classType' => 'required|string|max:100',
            'time' => 'required',
            'date' => 'required|date'
        ]);

        /*[$isPast, $errorMessage] = $this->service->isClassInThePast($validated['date'], $validated['classType']);

        if ($isPast) {
            return response()->json($errorMessage, 400);
        }

        [$isValid, $errorMessage] = $this->service->validateFormData(
            $validated['date'],
            $validated['time'],
            $validated['classType']
        );

        if (!$isValid) {
            return response()->json($errorMessage, 400);
        }*/

        //$reservation = Reservation::create($validated);
        $reservation = Reservation::create([
            'name'=> $request->name,
            'time'=> $request->time,
            'date'=> $request->date,
            'classType'=> $request->classType,
        ]);

        return response()->json($reservation);
    }

    public function update(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);

        $data = $this->service->updateMutableDataWithCorrectTimeFormat($request->all());

        $request->merge($data);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'classType' => 'required|string|max:100',
            'time' => 'required',
            'date' => 'required|date'
        ]);

        [$isValid, $errorMessage] = $this->service->validateFormData(
            $validated['date'],
            $validated['time'],
            $validated['classType']
        );

        if (!$isValid) {
            return response()->json($errorMessage, 400);
        }

        $reservation->update($validated);

        return response()->json($reservation, 200);
    }

    public function destroy($id)
    {
        $reservation = Reservation::findOrFail($id);

        $reservation->delete();

        return response()->json(null, 204);
    }
}