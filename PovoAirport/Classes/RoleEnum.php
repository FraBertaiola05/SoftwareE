<?php

//Enum representing the possible user roles in the system
enum RoleEnum: string
{
    case TowerController = 'tower_controller';
    case Pilot = 'pilot';
    case GroundCrew = 'ground_crew';
    case GateAgent = 'gate_agent';
    case AirlineCompanyManager = 'airline_company_manager';
    case AirportAnalyst = 'airport_analyst';
    case SystemAdmin = 'system_admin';
}
