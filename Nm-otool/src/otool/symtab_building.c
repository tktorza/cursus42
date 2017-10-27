/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   symtab_building.c                                  :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/10/27 14:05:07 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/27 14:05:18 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../../includes/nm_tool.h"

static void		print_res(long unsigned int addr, unsigned int size, char *ptr)
{
	unsigned int	i;
	char			*str;

	i = 0;
	while (i < size)
	{
		if (i == 0 || i % 16 == 0)
		{
			if (i != 0)
				addr += 16;
			ft_printf("%016llx\t", addr);
		}
		str = ft_itoa_base_sub(ptr[i], 16, 2);
		ft_printf("%s ", str ? str : "00");
		free(str);
		if ((i + 1) % 16 == 0 && i + 1 < size)
			write(1, "\n", 1);
		i++;
	}
	write(1, "\n", 1);
}

static void		symtab_building_bis(t_symtab *symt,
	struct segment_command_64 *seg, struct section_64 *sect,
	struct mach_header_64 *header)
{
	symt->i = 0;
	while (symt->i < seg->nsects)
	{
		if (ft_strcmp(sect->sectname, SECT_TEXT) == 0 &&
				ft_strcmp(sect->segname, SEG_TEXT) == 0)
		{
			ft_putstr("Contents of (__TEXT,__text) section\n");
			print_res(sect->addr, sect->size, (char *)header + sect->offset);
			symt->text = symt->ns;
		}
		else if (ft_strcmp(sect->sectname, SECT_DATA) == 0 &&
				ft_strcmp(sect->segname, SEG_DATA) == 0)
		{
			symt->data = symt->ns;
		}
		else if (ft_strcmp(sect->sectname, SECT_BSS) == 0 &&
				ft_strcmp(sect->segname, SEG_DATA) == 0)
			symt->bss = symt->ns;
		sect = (void *)sect + sizeof(*sect);
		symt->ns++;
		symt->i++;
	}
}

static void		symtab_building_bis_32(t_symtab *symt,
	struct segment_command *seg, struct section *sect,
	struct mach_header *header)
{
	symt->i = 0;
	while (symt->i < seg->nsects)
	{
		if (ft_strcmp(sect->sectname, SECT_TEXT) == 0 &&
				ft_strcmp(sect->segname, SEG_TEXT) == 0)
		{
			ft_putstr("Contents of (__TEXT,__text) section\n");
			print_res(sect->addr, sect->size, (char *)header + sect->offset);
			symt->text = symt->ns;
		}
		else if (ft_strcmp(sect->sectname, SECT_DATA) == 0 &&
				ft_strcmp(sect->segname, SEG_DATA) == 0)
		{
			symt->data = symt->ns;
		}
		else if (ft_strcmp(sect->sectname, SECT_BSS) == 0 &&
				ft_strcmp(sect->segname, SEG_DATA) == 0)
			symt->bss = symt->ns;
		sect = (void *)sect + sizeof(*sect);
		symt->ns++;
		symt->i++;
	}
}

void			symtab_building_32(t_symtab *symt,
	struct mach_header *header, struct load_command *lc)
{
	struct segment_command	*seg;
	struct section			*sect;

	while (symt->j < header->ncmds)
	{
		if (lc->cmd == LC_SEGMENT)
		{
			seg = (struct segment_command *)lc;
			sect = (struct section *)((void *)seg + sizeof(*seg));
			symtab_building_bis_32(symt, seg, sect, header);
		}
		lc = (void *)lc + lc->cmdsize;
		symt->j++;
	}
}

void			symtab_building(t_symtab *symt,
	struct mach_header_64 *header, struct load_command *lc)
{
	struct segment_command_64	*seg;
	struct section_64			*sect;

	symt->j = 0;
	while (symt->j < header->ncmds)
	{
		if (lc->cmd == LC_SEGMENT_64)
		{
			seg = (struct segment_command_64 *)lc;
			sect = (struct section_64 *)((void *)seg + sizeof(*seg));
			symtab_building_bis(symt, seg, sect, header);
		}
		lc = (void *)lc + lc->cmdsize;
		symt->j++;
	}
}
