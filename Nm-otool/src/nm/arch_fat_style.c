/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   arch_fat_style.c                                   :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/10/27 12:50:52 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/27 12:50:55 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../../includes/nm_tool.h"

static uint32_t	swap_uint32(uint32_t val)
{
	val = ((val << 8) & 0xFF00FF00) | ((val >> 8) & 0xFF00FF);
	return (val << 16) | (val >> 16);
}

void			handle_fat(char *ptr, char *file, t_symtab *symt)
{
	struct fat_header		*fheader;
	struct fat_arch			*arch;
	struct mach_header64	*header;
	uint32_t				offset;
	uint32_t				i;

	fheader = (struct fat_header *)ptr;
	arch = (void*)fheader + sizeof(*fheader);
	VERIF((void *)arch, (void *)fheader);
	offset = swap_uint32(arch->offset);
	i = 0;
	while (i < swap_uint32(fheader->nfat_arch))
	{
		offset = swap_uint32(arch->offset);
		header = (void*)ptr + offset;
		VERIF((void *)header, NULL);
		if (swap_uint32(arch->cputype) == CPU_TYPE_X86_64)
			break ;
		arch = (void *)arch + sizeof(*arch);
		VERIF((void *)arch, NULL);
		i++;
	}
	header = (void *)ptr + offset;
	VERIF((void *)header, NULL);
	type_bin((void *)header, file, symt, symt->bonus);
}
