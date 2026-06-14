<?php

declare(strict_types=1);

namespace Rede\Enum;

/**
 * Which Merchant Plug-In performs the 3DS 2.0 authentication. MPI Rede runs the
 * embedded flow; a third-party MPI supplies its own authentication data.
 */
enum Mpi: string
{
    case Rede = 'mpi_rede';
    case ThirdParty = 'mpi_third_party';
}
